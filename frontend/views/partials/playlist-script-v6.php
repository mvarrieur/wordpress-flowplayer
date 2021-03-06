<?php
$video_formats = array(
	'wemb'  => 'video/webm',
	'mp4'   => 'video/mp4',
	'ogg'   => 'video/ogg',
	'flash' => 'video/flash',
	'hls' => 'application/x-mpegurl',
);
// https://flowplayer.org/docs/playlist.html#javascript-install
foreach ( $atts as $video_id => $video ) {
	$sources = array();
	foreach ( $video_formats as $format => $type ) {
		if ( ! empty( $video['src'][ $type ] ) ) {
			$sources[] = array(
				'type' => esc_attr( $type ),
				'src' => esc_attr( $video['src'][ $type ] ),
			);
		}
	}
	$video['clip_config']['sources'] = $sources;
	$return[] = $video['clip_config'];
	$player_config = $video['player_config'];
}
$player_config['playlist'] = $return;
?>
<script>var fpPlaylist<?php echo absint( $first_video['playlist'] ); ?> = flowplayer('#jsplaylist<?php echo absint( $first_video['playlist'] ); ?>', <?php echo json_encode( $player_config ); ?> );</script>
