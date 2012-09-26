Optimized Fooman_Speedster BETA
===============================

**WARNING: do not install this in a live environment**

Inspired by a [blog post at FishPig](http://fishpig.co.uk/blog/why-you-shouldnt-merge-javascript-in-magento.html),
I started thinking about a way to optimize Magento's JS and CSS handling and minimize
the dowload size on each Magento page. Because Fooman already has a wonderful extension
called [Speedster](http://store.fooman.co.nz/magento-extension-speedster.html) to minify
and combine CSS and JS files, it made sense to me to enhance that extension.

The idea is to add a *bundle* of most used files to the head of each page. These files
will be combined and minified and added as a separate tag that gets loaded on every page.
All other files that get added via layout XML are also added, but as a separate tag.
This way there is one tag in the header that includes all big files (e.g. prototype.js for JS,
default.css for CSS) and one tag that includes all page specific (mostly small) files.

I put it on Github because I think it's in a beta stage and I would like to hear comments
from other developers. If the functionality is ready, I hope Kris will include the functionality
in his wonderful extension. It's not at all my intention to create a separate extension,
it's my intention to ask your feedback on this specific functionality and then, when it's ready,
hopefully have it included in the Fooman_Speedster extension.

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
- Made URLs max

How to add the bundle?
----------------------
Do it in local.xml of your theme:
```xml
<?xml version="1.0"?>
<layout version="0.1.0">
    <default>
        <reference name="head">
            <action method="setBundleItems">
                <type>skin_js</type>
                <!-- add site specific items here -->
            </action>
            <action method="setBundleItems">
                <type>skin_css</type>
                <!-- default magento -->
                <name>css/styles.css</name>
                <name>css/widgets.css</name>
                <!-- add site specific items here -->
            </action>
            <action method="setBundleItems">
                <type>js</type>
                <!-- default magento -->
                <name>prototype/prototype.js</name>
                <name>lib/ccard.js</name>
                <name>prototype/validation.js</name>
                <name>scriptaculous/builder.js</name>
                <name>scriptaculous/effects.js</name>
                <name>scriptaculous/dragdrop.js</name>
                <name>scriptaculous/controls.js</name>
                <name>scriptaculous/slider.js</name>
                <name>varien/js.js</name>
                <name>varien/form.js</name>
                <name>varien/menu.js</name>
                <name>mage/translate.js</name>
                <name>mage/cookies.js</name>
                <name>varien/weee.js</name>
                <!-- add site specific items here -->
            </action>
        </reference>
    </default>
</layout>
```

Todo
----
- Add better handling of Merge CSS/JS settings in Magento backend. If CSS merging is
  disabled, JS should still be merged by Speedster.
- Make it possible to add a file to the bundle so we can, for instance, add weee.js based
  on Magento backend setting (with an `ifconfig` parameter).
- Add a layout xml file to the module which adds a default bundle based on default/default
  theme. Of course, then it must be possible to add a single CSS/JS to the bundle (see
  above item)

Ideas
-----
- Make it possible to add multiple bundles, e.g. for catalog related pages (which includes
  product.js, configurable.js, bundle.js, ...)
