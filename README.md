[![Build](https://github.com/concrete5-community/blocks_cloner/actions/workflows/build.yml/badge.svg)](https://github.com/concrete5-community/blocks_cloner/actions/workflows/build.yml)

# Blocks Cloner

This repository contains a package for [ConcreteCMS](https://www.concretecms.org/) that lets you copy blocks, custom styles, and page attributes between two instances of Concrete.

This works by copying and pasting XML text in the so-called [CIF format](https://documentation.concretecms.org/9-x/developers/security/concrete-interchange-format).

This is also useful for package developers: they can test if new blocks and attributes correctly support exporting and importing their data.

## Installation Methods

* To support the author, you can install Blocks Cloner on recent versions of ConcreteCMS through the ConcreteCMS Marketplace - see https://market.concretecms.com/products/blocks-cloner/894b8f94-0f0b-11f0-abb4-0e1cf28cdc53
* For composer-based Concrete instances, simply run
   ```sh
   composer require concrete5-community/blocks_cloner
   ```
* Manual installation:
  1. download a `blocks_cloner-v….zip` file from the [releases page](https://github.com/concrete5-community/blocks_cloner/releases/latest)
  2. extract the zip file in your `packages` directory

Then, you have to login in your Concrete website, go to the Dashboard > Extend Concrete > Add Functionality, and install the package.

## Usage

Simply enter the Edit Mode of a website page.

### Exporting Attributes, Blocks or Areas

Click the "Export as XML" icon you see in the toolbar.
A panel will appear where you can choose the item to export: page attributes, or specific areas or blocks by selecting them from the displayed page structure.

### Exporting Single Blocks

Click on a block, then click the "Export block as XML" menu entry to view and copy the CIF representing the block.

### Exporting Area Styles and/or Blocks

Click on an area handle, then click the "Export area as XML" menu entry to view and copy the CIF representing the area style and or all the blocks it contains.

### Importing Page Attributes, Blocks or Areas

Click the "Import from XML" icon you see in the toolbar.
A panel will appear where:
- you can choose to import page attributes
- you can also see all the page areas: click one of them to import blocks and/or area styles in the selected area.

### Importing Blocks and Area Styles

You can import blocks and/or custom area styles into a specific area by clicking its handle: the context menu will display a new "Import content from XML" entry: click it to paste the CIF to be imported.

## Do you really want to say thank you?

You can offer me a [monthly coffee](https://github.com/sponsors/mlocati) or a [one-time coffee](https://paypal.me/mlocati) :wink:
