<?php
/**
 * Coverage for the two pure functions in includes/Helpers.php.
 */

use AdvancedTestimonial\Helpers;
use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase {

	/**
	 * @dataProvider rating_provider
	 */
	public function test_clamp_rating( $input, $expected ) {
		$this->assertSame( $expected, Helpers::clamp_rating( $input ) );
	}

	public function rating_provider() {
		return array(
			'zero stays zero (means no rating)' => array( 0, 0.0 ),
			'negative floors at zero'           => array( -3, 0.0 ),
			'above five caps'                   => array( 9, 5.0 ),
			'whole number passes through'       => array( 4, 4.0 ),
			'rounds to nearest half up'         => array( 4.3, 4.5 ),
			'rounds to nearest half down'       => array( 4.2, 4.0 ),
			'numeric string works'              => array( '3.5', 3.5 ),
			'non-numeric is no rating'          => array( 'great', 0.0 ),
			'null is no rating'                 => array( null, 0.0 ),
		);
	}

	/**
	 * @dataProvider video_provider
	 */
	public function test_parse_video( $url, $expected_type, $expected_embed_contains ) {
		$result = Helpers::parse_video( $url );

		if ( null === $expected_type ) {
			$this->assertNull( $result );
			return;
		}

		$this->assertSame( $expected_type, $result['type'] );
		$this->assertStringContainsString( $expected_embed_contains, $result['embed'] );
	}

	public function video_provider() {
		return array(
			'youtube watch'        => array( 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'youtube', 'youtube-nocookie.com/embed/dQw4w9WgXcQ' ),
			'youtube short link'   => array( 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 'youtube-nocookie.com/embed/dQw4w9WgXcQ' ),
			'youtube shorts'       => array( 'https://www.youtube.com/shorts/dQw4w9WgXcQ', 'youtube', 'youtube-nocookie.com/embed/dQw4w9WgXcQ' ),
			'youtube embed'        => array( 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'youtube', 'youtube-nocookie.com/embed/dQw4w9WgXcQ' ),
			'vimeo plain'          => array( 'https://vimeo.com/123456789', 'vimeo', 'player.vimeo.com/video/123456789' ),
			'vimeo with hash'      => array( 'https://vimeo.com/123456789/abcdef1234', 'vimeo', 'h=abcdef1234' ),
			'vimeo player url'     => array( 'https://vimeo.com/video/123456789', 'vimeo', 'player.vimeo.com/video/123456789' ),
			'mp4 file'             => array( 'https://example.com/clip.mp4', 'file', 'clip.mp4' ),
			'webm uppercase ext'   => array( 'https://example.com/CLIP.WEBM?x=1', 'file', 'CLIP.WEBM' ),
			'ogv file'             => array( 'https://example.com/clip.ogv', 'file', 'clip.ogv' ),
			'empty is null'        => array( '', null, '' ),
			'whitespace is null'   => array( '   ', null, '' ),
			'random url is null'   => array( 'https://example.com/about', null, '' ),
			'mp3 is not video'     => array( 'https://example.com/song.mp3', null, '' ),
			'short id rejected'    => array( 'https://youtu.be/abc', null, '' ),
		);
	}

	public function test_parse_video_youtube_thumbnail() {
		$result = Helpers::parse_video( 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' );

		$this->assertSame( 'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $result['thumbnail'] );
	}
}
