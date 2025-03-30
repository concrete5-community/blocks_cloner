[![Build](https://github.com/concrete5-community/blocks_cloner/actions/workflows/build.yml/badge.svg)](https://github.com/concrete5-community/blocks_cloner/actions/workflows/build.yml)

# Blocks Cloner

This repository contains a package for [ConcreteCMS](https://www.concretecms.org/) that lets you copy blocks between two instances of Concrete.

This works by copying and pasting XML text in the so-called [CIF format](https://documentation.concretecms.org/9-x/developers/security/concrete-interchange-format).

This is also useful for package developers: they can test if new blocks correctly support exporting and importing their data.

## Installation

For composer-based Concrete instances, simply run

```sh
composer require concrete5-community/blocks_cloner
```

Otherwise, you can:
1. download a `blocks_cloner-v….zip` file from the [releases page](https://github.com/concrete5-community/blocks_cloner/releases/latest)
2. extract the zip file in your `packages` directory

Then, you have to login in your Concrete website, go to the Dashboard > Extend Concrete > Add Functionality, and install the package.

## Usage

Simply enter the Edit Mode of a website page.

### Exporting Single Blocks

When clicking on a block, the context menu will contain a new "Export Block as XML" entry: click it to copy and paste the CIF text.

### Exporting Area Styles and/or Blocks

You can also export custom area styles, as well as all the blocks in an area.
To do so, simply click on the area handle: you'll see a new "Export Area as XML" menu item: click on it to export the custom area style and/or the blocks it contains.

### Exporting Blocks and Areas 

You can choose an area or block to export by selecting it from the page structure.
Do to so, simply click the "Export as XML" icon you see in the toolbar.
A panel will appear where you can choose the item to export.


### Importing Blocks and Area Styles

You can import blocks and/or custom area styles into an area by clicking its handle: the context menu will display a new "Import from XML" entry: click it to paste the CIF to be importer.

In the toolbar you'll also see a new "Import from XML" icon: if you click it, you'll see a panel where you can see all the areas in the page. To add data to a specific area, simply click it.

## Do you really want to say thank you?

You can offer me a [monthly coffee](https://github.com/sponsors/mlocati) or a [one-time coffee](https://paypal.me/mlocati) :wink:
