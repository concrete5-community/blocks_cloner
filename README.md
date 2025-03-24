[![Build](https://github.com/concrete5-community/blocks_cloner/actions/workflows/build.yml/badge.svg)](https://github.com/concrete5-community/blocks_cloner/actions/workflows/build.yml)

# Blocks Cloner

This repository contains a package for [ConcreteCMS](https://www.concretecms.org/) that lets you copy blocks between two instances of Concrete.

This works by copying and pasting text in the so-called [CIF format](https://documentation.concretecms.org/9-x/developers/security/concrete-interchange-format).

This is also useful for package developers: they can test if new blocks correctly support exporting and importing.

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

### Exporting Blocks

When clicking on a block, the context menu will contain a new "Export as XML" entry: click it to copy and paste the CIF.

You'll also see a new "Export Block as XML" icon in the toolbar: if you click it, you'll see a panel that lets you view the full structure of the page.
Click on a block of that structure to export its CIF.

### Importing Blocks

You can import a block into an area by clicking its handle: the context menu will display a new "Import Block from XML" entry: click it to paste the CIF of the block to be added.

In the toolbar you'll also see a new "Import block from XML" icon: if you click it, you'll see a panel where you can see all the areas in the page. To add a block to a specific area, simply click it.

## Do you really want to say thank you?

You can offer me a [monthly coffee](https://github.com/sponsors/mlocati) or a [one-time coffee](https://paypal.me/mlocati) :wink:
