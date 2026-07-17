[![Continuous Integration](https://github.com/concrete5-community/blocks_cloner/actions/workflows/ci.yml/badge.svg)](https://github.com/concrete5-community/blocks_cloner/actions/workflows/ci.yml)

# Blocks Cloner

This repository contains a package for [ConcreteCMS](https://www.concretecms.org/) that lets you copy blocks, custom styles, and page attributes between two instances of Concrete.

This is very useful for example if you want to copy data from a staging to a production site, as well as if you need to quickly copy some content from an old Concrete version (at least 8.5.2) to a new one.

This is also useful for package developers: they can test if new blocks and attributes correctly support exporting and importing their data.

This works by copying and pasting XML text in the so-called [CIF format](https://documentation.concretecms.org/9-x/developers/security/concrete-interchange-format).

If the source data uses images, you'll be able to download those images from the source website and upload them to the destination website with just a couple of clicks.

The package also offers 3 panels:

- one for exporting blocks, areas, and page attributes by browsing the page structure
- one for importing page attributes and content to a specific area by browsing the page structure
- one for viewing the whole page structure, with an easy access to context menus

The package also provides a dashboard page, where you can turn on/off the Import, the Export, or the Page Structure features.

## Usage

Simply enter the Edit Mode of a website page, and click the toolbar buttons or use the block/area context menus.

### Exporting Attributes, Blocks or Areas

Click the "Export as XML" toolbar icon.

A panel will appear where you can choose the item to export: page attributes, or specific areas or blocks by selecting them from the displayed page structure.

### Exporting Single Blocks

Click on a block, then click the "Export block as XML" menu entry to view and copy the CIF representing the block.

### Exporting Areas (with styles and/or blocks)

Click on an area handle, then click the "Export area as XML" menu entry to view and copy the CIF representing the area style and or all the blocks it contains.

### Importing Page Attributes, Blocks or Areas

Click the "Import from XML" toolbar icon.
A panel will appear where:

- you can choose to import page attributes
- you can also see all the page areas: click one of them to import blocks and/or area styles in the selected area.

### Importing Blocks and Area Styles

You can import blocks and/or custom area styles into a specific area by clicking its handle: the context menu will display a new "Import content from XML" entry: click it to paste the CIF to be imported.

## Installation Methods

* To support the author, you can install Blocks Cloner on recent versions of ConcreteCMS [through the ConcreteCMS Marketplace](https://market.concretecms.com/products/blocks-cloner/894b8f94-0f0b-11f0-abb4-0e1cf28cdc53).
* For composer-based Concrete instances, simply run
   ```sh
   composer require concrete5-community/blocks_cloner
   ```
* Manual installation:
  1. download a `blocks_cloner-v….zip` file from the [releases page](https://github.com/concrete5-community/blocks_cloner/releases/latest)
  2. extract the zip file in your `packages` directory

Then, you have to login in your Concrete website, go to the Dashboard > Extend Concrete > Add Functionality, and install the package.

## Do you really want to say thank you?

You can offer me a [monthly coffee](https://github.com/sponsors/mlocati) or a [one-time coffee](https://paypal.me/mlocati) :wink:
