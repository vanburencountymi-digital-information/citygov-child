<?php
/**
 * Partial: Podcast Player
 *
 * Supports:
 *  - WP ≥ 5.5 args API:  get_template_part( 'partials/podcast-player', null, [ 'audio_src'=> $url, … ] );
 *  - Older WP:          set_query_var( 'audio_src', $url ); get_template_part( 'partials/podcast-player' );
 */

// normalize args and apply fallbacks
$defaults = [
  // 1) audio_src: from args or fall back to query‐var
  'audio_src'  => get_query_var( 'audio_src', '' ),

  // 2) everything else must be passed in via args
  'cover'      => '',
  'title'      => '',
  'host'       => '',
  'episode'    => '',
  'transcript' => '',
];

$args      = wp_parse_args( $args, $defaults );
$audio_src = $args['audio_src'];
$cover      = $args['cover'];
$title      = $args['title'];
$host       = $args['host'];
$episode    = $args['episode'];
$transcript = $args['transcript'];

// 3) bail if *all* are empty
if ( ! $audio_src && ! $cover && ! $title && ! $host && ! $episode && ! $transcript) {
    return;
}
?>

<div class="podcast-player sticky-sidebar-item">

  <?php if ( $cover || $title || $host || $episode ) : ?>
  <div class="podcast-info">

    <?php if ( $cover ) : ?>
    <img
      src="<?php echo esc_url( $cover ); ?>"
      alt="<?php echo esc_attr( $title ?: 'Podcast Cover' ); ?>"
      class="podcast-cover"
    >
    <?php endif; ?>

    <div class="podcast-meta">
      <?php if ( $title )   : ?><h2 class="podcast-title"><?php   echo esc_html( $title );   ?></h2><?php endif; ?>
      <?php if ( $host )    : ?><p class="podcast-host">Host: <?php echo esc_html( $host );    ?></p><?php endif; ?>
      <?php if ( $episode ) : ?><p class="podcast-episode">Episode <?php echo esc_html( $episode ); ?></p><?php endif; ?>
    </div>

  </div>
  <?php endif; ?>

  <?php if ( $audio_src ) : ?>
  <div class="progress-container">
    <input type="range" id="progressBar" value="0" step="1" min="0" style="background-size: 0% 100%">
    <div class="time-display">
      <span id="currentTime">0:00</span> /
      <span id="duration">0:00</span>
    </div>
  </div>

  <div class="controls">
    <button id="skipBack" title="Back 15s">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <!-- Number "15" -->
        <text x="2" y="12" font-size="7" font-family="Arial" fill="currentColor" font-weight="bold">15</text>
        
        <!-- Backward arrow -->
        <path d="M14,3.5 C12,1.5 9,1 6.5,2 L6.5,0.5 L2.5,4 L6.5,7.5 L6.5,5.5 C8.5,4.5 10.5,5 12,7 C13.5,9 13,12 10.5,13.5" 
              stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/>
      </svg>
      <span class="sr-only">Rewind 15 seconds</span>
    </button>
    <button id="playPause" title="Play/Pause">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="play-icon" viewBox="0 0 16 16">
        <path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393z"/>
      </svg>
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="pause-icon" viewBox="0 0 16 16" style="display: none;">
        <path d="M5.5 3.5A1.5 1.5 0 0 1 7 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5zm5 0A1.5 1.5 0 0 1 12 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5z"/>
      </svg>
      <span class="sr-only">Play or pause</span>
    </button>
    <button id="skipForward" title="Forward 15s">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <!-- Number "15" -->
        <text x="2" y="12" font-size="7" font-family="Arial" fill="currentColor" font-weight="bold">15</text>
        
        <!-- Forward arrow -->
        <path d="M2,3.5 C4,1.5 7,1 9.5,2 L9.5,0.5 L13.5,4 L9.5,7.5 L9.5,5.5 C7.5,4.5 5.5,5 4,7 C2.5,9 3,12 5.5,13.5" 
              stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/>
      </svg>
      <span class="sr-only">Forward 15 seconds</span>
    </button>
  </div>

  <div class="volume-settings">

    <div class="volume-control">
      <label for="volumeSlider" class="control-label">Volume:</label>
      <input
        type="range"
        id="volumeSlider"
        min="0"
        max="1"
        step="0.05"
        value="0.75"
      >
    </div>

    <div class="speed-control">
      <label for="speedSelector" class="control-label">Speed:</label>
      <select id="speedSelector">
        <option value="0.5">0.5×</option>
        <option value="0.75">0.75×</option>
        <option value="1" selected>1×</option>
        <option value="1.25">1.25×</option>
        <option value="1.5">1.5×</option>
        <option value="2">2×</option>
      </select>
    </div>

  </div>

  <audio id="audio" preload="metadata" src="<?php echo esc_url( $audio_src ); ?>">
    <?php esc_html_e( 'Your browser does not support the audio element.', 'citygov-child' ); ?>
  </audio>
  <?php endif; ?>

  <?php if ( $transcript || $audio_src ) : ?>
  <div class="downloads">
    <?php if ( $transcript ) : ?>
      <a href="<?php echo esc_url( $transcript ); ?>" download class="download-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="download-icon">
          <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
          <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
        </svg>
        Transcript
      </a>
    <?php endif; ?>

    <?php if ( $audio_src ) : ?>
      <a href="<?php echo esc_url( $audio_src ); ?>" download class="download-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="download-icon">
          <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
          <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
        </svg>
        Audio
      </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>