<?php
/**
 * Class Elite_Video_Player_Sources_Checks
 * Contains all checkers for check videos
 *
 * @since 1.0.0
 */

class Elite_Video_Player_Sources_Checks {

	/**
	 * Check target source
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @return bool|string
	 */
	public static function check_source( $source ) {

		$is_google_drive = strpos( $source, 'googleapis.com' ) !== false;
		if ( $is_google_drive && self::remote_file_exists( $source ) ) {
			return 'html5';
		}

		$is_youtube = strpos( $source, 'youtube.com' ) !== false
		              || strpos( $source, 'youtu.be' ) !== false;
		if ( $is_youtube && self::check_youtube_video_exists( $source ) ) {
			return 'youtube';
		}

		$is_dropbox = strpos( $source, 'dropbox' ) !== false;
		if ( $is_dropbox && self::check_dropbox_exists( $source ) ) {
			return 'dropbox';
		}

		return false;

	}

	/**
	 * Checking youtube video
	 *
	 * @since 1.0.0
	 *
	 * @param $video_url
	 *
	 * @return bool
	 */
	private static function check_youtube_video_exists( $video_url ) {

		$video_url = 'https://www.youtube.com/oembed?url=' . $video_url . '&format=json';
		$headers   = @get_headers( $video_url );

		return ( strpos( $headers[0], '200' ) > 0 ) ? true : false;
	}

	/**
	 * Checking dropbox video
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @return bool
	 */
	private static function check_dropbox_exists( $source ) {

		$source = self::source_to_dropbox( $source );
		$title  = self::get_page_title( $source );

		if ( strpos( $title, 'Link not found' ) !== false ) {
			return false;
		}

		return true;
	}

	/**
	 * Convert dropbox share link to dl.dropboxusercontent.com
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @return string
	 */
	public static function source_to_dropbox_user_content( $source ) {

		if ( strpos( $source, 'dl.dropboxusercontent.com' ) ) {
			return $source;
		}

		if ( strpos( $source, 'dropbox.com' ) ) {
			$exploded_source = explode( 'dropbox.com/', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://dl.dropboxusercontent.com/' . $exploded_source[1];
			}
		}

		return '';
	}

	/**
	 * Convert drobdoxusercontent link to dropbox
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @return string
	 */
	public static function source_to_dropbox( $source ) {

		if ( strpos( $source, 'dropbox.com' ) ) {
			return $source;
		}

		if ( strpos( $source, 'dropboxusercontent.com' ) ) {
			$exploded_source = explode( 'dropboxusercontent.com/', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://www.dropbox.com/' . $exploded_source[1];
			}
		}

		return '';
	}

	/**
	 * Check remote file
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @return bool
	 */
	private static function remote_file_exists( $source ) {

		$ch = curl_init( $source );
		curl_setopt( $ch, CURLOPT_NOBODY, true );
		curl_exec( $ch );
		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );
		if ( $httpCode == 200 ) {
			return true;
		}

		return false;
	}

	/**
	 * Get title of remote page
	 *
	 * @param $source
	 *
	 * @return mixed
	 */
	private static function get_page_title( $source ) {

		$str = file_get_contents( $source );
		if ( strlen( $str ) > 0 ) {
			$str = trim( preg_replace( '/\s+/', ' ', $str ) );
			preg_match( "/\<title\>(.*)\<\/title\>/i", $str, $title );

			return $title[1];
		}

		return '';
	}

}