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
			return 'googledrive';
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

		$is_archive_org = strpos( $source, 'archive.org' ) !== false;
		if ( $is_archive_org && self::check_archive_exists( $source ) ) {
			return 'archive';
		}

		$is_bitchute = strpos( $source, 'bitchute.com' ) !== false;
		if ( $is_bitchute && self::check_bitchute_exists( $source ) ) {
			return 'bitchute';
		}

		$is_vidlii = strpos( $source, 'vidlii.com' ) !== false;
		if ( $is_vidlii && self::check_vidlii_exists( $source ) ) {
			return 'vidlii';
		}

		$is_mega = strpos( $source, 'mega.nz' ) !== false;
		if ( $is_mega && self::check_mega_exists( $source ) ) {
			return 'mega';
		}

		return false;

	}

	/**
	 * Checking youtube video
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @return bool
	 */
	private static function check_youtube_video_exists( $source ) {

		$source    = self::prepare_source_youtube( $source );
		$video_url = 'https://www.youtube.com/oembed?url=' . $source . '&format=json';
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

		$source = self::prepare_source_dropbox( $source );
		$title  = self::get_page_title( $source );

		if ( strpos( $title, 'Link not found' ) !== false
		     || strpos( $title, 'Error' ) !== false ) {
			return false;
		}

		return true;
	}

	/**
	 * Checking Archive.org video
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @return bool
	 */
	private static function check_archive_exists( $source ) {

		$source = self::prepare_source_archive( $source, true );
		$title  = self::get_page_title( $source );

		if ( empty( $title ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checking Bitchute.com video
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @return bool
	 */
	private static function check_bitchute_exists( $source ) {

		$source = self::prepare_source_bitchute( $source, true );
		$title  = self::get_page_title( $source );

		if ( empty( $title ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checking vidlii.com
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @return bool
	 */
	private static function check_vidlii_exists( $source ) {

		$source = self::prepare_source_vidlii( $source );
		$title  = self::get_page_title( $source );

		if ( strpos( $title, 'Display Yourself' ) !== false ) {
			return false;
		}

		return true;
	}

	/**
	 * Checking mega video
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @return bool
	 */
	private static function check_mega_exists( $source ) {

		$source          = self::prepare_source_mega( $source, true );
		$exploded_source = explode( 'embed/', $source );

		if ( isset( $exploded_source[1] ) ) {
			$file_key = explode( '#', $exploded_source[1] );
			if ( isset( $file_key[0] ) ) {
				$req = [
					'a' => 'g',
					'g' => 1,
					'p' => $file_key[0],
				];

				$ch = curl_init( 'https://g.api.mega.co.nz/cs?id=1' );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_POST, true );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( array( $req ) ) );
				$resp = curl_exec( $ch );
				curl_close( $ch );
				$resp = json_decode( $resp, true );

				if ( isset( $resp[0]['msd'] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Convert dropbox share link to dl.dropboxusercontent.com
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @param bool $to_embed
	 *
	 * @return string
	 */
	public static function prepare_source_youtube( $source, $to_embed = false ) {

		if ( $to_embed && strpos( $source, 'youtube.com/embed' ) !== false
		     || ! $to_embed && strpos( $source, 'youtube.com/watch' ) !== false ) {
			return $source;
		}

		if ( $to_embed && strpos( $source, 'youtube.com/watch' ) !== false ) {
			$exploded_source = explode( 'youtube.com/watch?v=', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://www.youtube.com/embed/' . $exploded_source[1];
			}
		} elseif ( ! $to_embed && strpos( $source, 'youtube.com/embed' ) !== false ) {
			$exploded_source = explode( 'youtube.com/embed/', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://www.youtube.com/watch?v=' . $exploded_source[1];
			}
		}

		return false;
	}

	/**
	 * Convert dropbox share link to dl.dropboxusercontent.com
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @param bool $to_embed
	 *
	 * @return string
	 */
	public static function prepare_source_dropbox( $source, $to_embed = false ) {

		if ( $to_embed && strpos( $source, 'dl.dropboxusercontent.com' ) !== false
		     || ! $to_embed && strpos( $source, 'dropbox.com' ) !== false ) {
			return $source;
		}

		if ( $to_embed && strpos( $source, 'dropbox.com' ) !== false ) {
			$exploded_source = explode( 'dropbox.com/', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://dl.dropboxusercontent.com/' . $exploded_source[1];
			}
		} elseif ( ! $to_embed && strpos( $source, 'dl.dropboxusercontent.com' ) !== false ) {
			$exploded_source = explode( 'dl.dropboxusercontent.com/', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://www.dropbox.com/' . $exploded_source[1];
			}
		}

		return false;
	}

	/**
	 * Convert link for archive to embed or not
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @param bool $to_embed
	 *
	 * @return string
	 */
	public static function prepare_source_archive( $source, $to_embed = false ) {

		if ( $to_embed && strpos( $source, 'archive.org/embed' ) !== false
		     || ! $to_embed && strpos( $source, 'archive.org/details' ) !== false ) {
			return $source;
		}

		if ( $to_embed && strpos( $source, 'archive.org/details' ) !== false ) {
			$exploded_source = explode( 'archive.org/details/', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://archive.org/embed/' . $exploded_source[1];
			}
		} elseif ( ! $to_embed && strpos( $source, 'archive.org/embed' ) !== false ) {
			$exploded_source = explode( 'archive.org/embed/', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://archive.org/details/' . $exploded_source[1];
			}
		}

		return false;
	}

	/**
	 * Convert link for mega to embed or not
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @param bool $to_embed
	 *
	 * @return string
	 */
	public static function prepare_source_mega( $source, $to_embed = false ) {

		if ( $to_embed && strpos( $source, 'mega.nz/embed' ) !== false
		     || ! $to_embed && strpos( $source, 'mega.nz/file' ) !== false ) {
			return $source;
		}

		if ( $to_embed && strpos( $source, 'mega.nz/file' ) !== false ) {
			$exploded_source = explode( 'mega.nz/file/', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://mega.nz/embed/' . $exploded_source[1];
			}
		} elseif ( ! $to_embed && strpos( $source, 'mega.nz/embed' ) !== false ) {
			$exploded_source = explode( 'mega.nz/embed/', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://mega.nz/file/' . $exploded_source[1];
			}
		}

		return false;
	}

	/**
	 * Convert link for bitchute.com to embed or not
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @param bool $to_embed
	 *
	 * @return string
	 */
	public static function prepare_source_bitchute( $source, $to_embed = false ) {

		if ( $to_embed && strpos( $source, 'bitchute.com/embed' ) !== false
		     || ! $to_embed && strpos( $source, 'bitchute.com/video' ) !== false ) {
			return $source;
		}

		if ( $to_embed && strpos( $source, 'bitchute.com/video' ) !== false ) {
			$exploded_source = explode( 'bitchute.com/video/', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://www.bitchute.com/embed/' . $exploded_source[1];
			}
		} elseif ( ! $to_embed && strpos( $source, 'bitchute.com/embed' ) !== false ) {
			$exploded_source = explode( 'bitchute.com/embed/', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://www.bitchute.com/video/' . $exploded_source[1];
			}
		}

		return false;
	}

	/**
	 * Convert link for vidlii.com to embed or not
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 *
	 * @param bool $to_embed
	 *
	 * @return string
	 */
	public static function prepare_source_vidlii( $source, $to_embed = false ) {

		if ( $to_embed && strpos( $source, 'vidlii.com/embed' ) !== false
		     || ! $to_embed && strpos( $source, 'vidlii.com/watch' ) !== false ) {
			return $source;
		}

		if ( $to_embed && strpos( $source, 'vidlii.com/watch' ) !== false ) {
			$exploded_source = explode( 'vidlii.com/watch', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://www.vidlii.com/embed' . $exploded_source[1];
			}
		} elseif ( ! $to_embed && strpos( $source, 'www.vidlii.com/embed' ) !== false ) {
			$exploded_source = explode( 'www.vidlii.com/embed', $source );

			if ( isset( $exploded_source[1] ) ) {
				return 'https://www.vidlii.com/watch' . $exploded_source[1];
			}
		}

		return false;
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

		if ( ! empty( $source ) ) {
			$response = wp_remote_get( $source, array(
				'timeout'     => 5,
				'redirection' => 5,
				'httpversion' => '1.0',
				'user-agent'  => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) 
				Chrome/72.0.3626.121 Safari/537.36',
				'blocking'    => true,
				'headers'     => array(),
				'cookies'     => array(),
				'body'        => null,
				'compress'    => false,
				'decompress'  => true,
				'sslverify'   => true,
				'stream'      => false,
				'filename'    => null
			) );

			if ( wp_remote_retrieve_response_code( $response ) === 200 ) {
				$body = wp_remote_retrieve_body( $response );

				if ( strlen( $body ) > 0 ) {
					$str = trim( preg_replace( '/\s+/', ' ', $body ) );
					preg_match( "/\<title\>(.*)\<\/title\>/i", $str, $title );

					return $title[1];
				}
			}
		}

		return '';
	}

	/**
	 * Get embed source from link
	 *
	 * @since 1.0.0
	 *
	 * @param $source
	 * @param $source_type
	 *
	 * @return string
	 */
	public static function get_embed_source( $source, $source_type ) {

		$embed_source = $source;

		switch ( $source_type ) {
			case 'youtube' :
				$embed_source = self::prepare_source_youtube( $source, true );
				break;
			case 'dropbox' :
				$embed_source = self::prepare_source_dropbox( $source, true );
				break;
			case 'archive' :
				$embed_source = self::prepare_source_archive( $source, true );
				break;
			case 'bitchute' :
				$embed_source = self::prepare_source_bitchute( $source, true );
				break;
			case 'vidlii' :
				$embed_source = self::prepare_source_vidlii( $source, true );
				break;
			case 'mega' :
				$embed_source = self::prepare_source_mega( $source, true );
				break;
		}

		return $embed_source;
	}

	/**
	 * Get youtube id from url
	 *
	 * @since 1.0.0
	 *
	 * @param $embed_source
	 *
	 * @return string
	 */
	public static function get_youtube_id( $embed_source ) {

		$request_string = explode('embed/', $embed_source);

		if (isset($request_string[1])) {
			$youtube_id = explode('\?', $request_string[1]);

			return $youtube_id[0];
		}

		return '';
	}
}