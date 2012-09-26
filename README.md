Optimized Fooman_Speedster BETA
===============================

- Changed *lib/minify/m.php* to use `$_SERVER['SCRIPT_FILENAME']` instead of `__FILE__`
  This is for correct handling of symlinks (`__FILE__` points to the file inside the
  *.modman* directory whereas `SCRIPT_FILENAME` points to the location of the symlink).
- Let the extension (partially) respect the 'Merge CSS' and 'Merge JS' settings
  in Magento backend for easier debugging of js/css. Partially, because for now
  I let it fall back to default Magento behaviour if one of the flags is turned
  off. That leads e.g. to a different (default Magento) js merging functionality
  if css merging is turned off.
- Let the developer define a bundle of js files that gets included as a separate
  script/link tag that gets included on every page. Leads to one big file that
  gets loaded by the client once, that gets cached and is only downloaded once
  for the entire visit.
- Added *modman* file for easy installation
