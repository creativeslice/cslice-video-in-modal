<?php // cslice-video-in-modal GitHub Plugin Updater 2024.12.03 - PUBLIC REPO
namespace CSlice\VideoInModal;

class Plugin_Updater {
    private $plugin_slug;
    private $plugin_data;
    private $github_api_url;

    public function __construct($plugin_file, $github_repo, $github_token) {
        $this->plugin_slug = plugin_basename($plugin_file);
        $this->github_api_url = "https://api.github.com/repos/{$github_repo}/releases/latest";

        add_action('init', function() use ($plugin_file) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            $this->plugin_data = get_plugin_data($plugin_file, false, true);

            add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_updates']);
            add_filter('upgrader_pre_download', [$this, 'download_package'], 10, 3);
        });
    }

    public function check_for_updates($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $release_info = $this->get_github_release_info();
        if ($release_info && version_compare(ltrim($release_info->tag_name, 'v'), $this->plugin_data['Version'], '>')) {
            $download_link = $this->get_download_link($release_info);
            if ($download_link) {
                $transient->response[$this->plugin_slug] = (object) [
                    'slug' => $this->plugin_slug,
                    'plugin' => $this->plugin_slug,
                    'new_version' => ltrim($release_info->tag_name, 'v'),
                    'package' => $download_link,
                    'tested' => $this->plugin_data['Tested up to'] ?? '',
                    'requires' => $this->plugin_data['Requires at least'] ?? '',
                    'requires_php' => $this->plugin_data['Requires PHP'] ?? '',
                ];
            }
        }

        return $transient;
    }

    private function get_github_release_info() {
        $response = wp_remote_get($this->github_api_url, [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version')
            ]
        ]);

        if (is_wp_error($response)) {
            return false;
        }

		$release_info = json_decode(wp_remote_retrieve_body($response));
		if (isset($release_info->tag_name)) {
			return $release_info;
		}

		return false;
    }

    private function get_download_link($release_info) {
        foreach ($release_info->assets as $asset) {
            if (substr($asset->name, -4) === '.zip') {
                return $asset->browser_download_url;
            }
        }
        return $release_info->zipball_url ?? null;
    }

    public function download_package($reply, $package, $upgrader) {
        if (strpos($package, 'github.com') === false) {
            return $reply;
        }

        $upgrader->skin->feedback("Starting download from GitHub...");

        $url_parts = parse_url($package);
        $path_parts = explode('/', trim($url_parts['path'], '/'));
        $api_url = "https://api.github.com/repos/{$path_parts[0]}/{$path_parts[1]}/releases/tags/{$path_parts[4]}";

        $response = wp_remote_get($api_url, [
            'headers' => [
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version')
            ]
        ]);

        if (is_wp_error($response)) {
            return new \WP_Error('release_info_failed', 'Failed to get release information.');
        }

        $release_info = json_decode(wp_remote_retrieve_body($response), true);
        $download_url = '';
        foreach ($release_info['assets'] as $asset) {
            if ($asset['name'] === basename($package)) {
                $download_url = $asset['url'];
                break;
            }
        }

        if (empty($download_url)) {
            return new \WP_Error('asset_not_found', 'The specified asset was not found in the release.');
        }

        $download_response = wp_remote_get($download_url, [
            'timeout' => 300,
            'stream' => true,
            'filename' => wp_tempnam('github_update'),
            'headers' => [
                'Accept' => 'application/octet-stream',
                'User-Agent' => 'WordPress/' . get_bloginfo('version')
            ]
        ]);

        if (is_wp_error($download_response)) {
            return new \WP_Error('download_failed', 'Failed to download the update package.');
        }

        return $download_response['filename'];
    }

    public function http_request_args($args, $url) {
        if (strpos($url, 'api.github.com') !== false || strpos($url, 'github.com') !== false) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->github_token;
        }
        return $args;
    }
}
