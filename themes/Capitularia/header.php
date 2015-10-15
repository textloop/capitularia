<!DOCTYPE html>

<html <?php language_attributes (); ?>>
  <head>
    <meta charset="<?php bloginfo ('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="pingback" href="<?php bloginfo ('pingback_url'); ?>" />

    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?php wp_title ('|', true, 'right'); ?></title>

    <?php
	/* Always have wp_head() just before the closing </head>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to add elements to <head> such
	 * as styles, scripts, and meta tags.
	 */
	wp_head ();
       ?>
  </head>

  <body <?php body_class (); ?>>
    <header id="header">

      <nav id="top-nav" class="top-nav horiz-nav ui-helper-clearfix">
	<?php wp_nav_menu (array ('theme_location' => 'navtop')); ?>
      </nav>

      <div id="bottom-header-wrapper">
	<a href="/" class="homelink">
	  <img <?php cap_theme_image ('Capitularia_Logo.png'); ?>
	       alt="Capitularia - Edition der fränkischen Herrschererlasse"/>
	</a>

	<form id="searchform" class="searchform" action="/" method="get">
	  <input id="searchinput"  class="sword"  type="text" placeholder="Suchen" name="s" />
	  <button id="searchsubmit" class="submit" type="submit" name="submit"></button>
	</form>

	<nav id="bottom-nav" class="bottom-nav horiz-nav ui-helper-clearfix">
	  <?php wp_nav_menu (array ('theme_location' => 'navbottom')); ?>
	</nav>
      </div>

    </header>
