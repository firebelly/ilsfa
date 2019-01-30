<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="<?= Roots\Sage\Assets\asset_path('images/favicon.png') ?>" />
  <?php wp_head(); ?>

  <?php if (WP_ENV === 'production'): ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-103560791-2"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'UA-103560791-2');
    </script>
    <!-- Global site tag (gtag.js) - Google Analytics -->
  <?php endif; ?>
</head>
