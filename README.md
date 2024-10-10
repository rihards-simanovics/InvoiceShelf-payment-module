# 📦 Public Archive: Repo migrated under official Invoice Shelf Namespace
This repo has been migrated under the official InvoiceShlf namespace
For further updates please go to: https://github.com/InvoiceShelf/module-payments

# Invoice Shelf - Payments Module

Adds ability to process Payments using your InvoiceShelf instance.

![Preview of Invoice Shelf, Payments Settings page](./preview.png)

## Table of Contents

- [Installation](#installation)
- [Update](#update)
  - [Hard Stops](#hard-stops)
- [Development](#development)
  - [Prerequisites](#prerequisites)
  - [Steps](#steps)
- [Troubleshooting](#troubleshooting)
- [Copyright](#copyright)

## Installation

1. Download the zip with pre-built module from the latest release in this repo. Alternatively you can build from source, for that follow the steps in the [development](#development) section.
2. Create `/Modules/` dir in your server InvoiceShelf project root. **NOTE:** make sure it's capitalised like in example, it's not a typo.
3. Upload the `Payments.zip` into the newly created `/Modules/` dir.
4. Unzip it.
5. In your server InvoiceShelf project `/` root dir, run `php artisan install:module Payments 1.1.0`.
6. (optional) If you have any issues with your installed module, try clearing your browser's `cache`, `cookies`, and/or `site data`.

## Update

1. Download the zip with pre-built module from the latest release in this repo. Alternatively you can build from source, for that follow the steps in the [development](#development) section.
2. Upload the `WhiteLabel.zip` into the newly created `/Modules/` dir.
3. Remove old `WhiteLabel/` module folder.
4. Unzip it and visit the site.

### Hard Stops

- InvoiceShelf >= 1.3.0 (upgrade this module to 1.1.0)

## Development

This is a step by step guide on how to get started, with development.

### Prerequisites

1. nvm/fnm (optional)
2. Node JS: `20 LTS`
3. `yarn`

### Steps

1. Clone the [InvoiceShelf repo](https://github.com/InvoiceShelf/InvoiceShelf) - needed for the admin components. In the future it's likely to be just a node package and you wont need this step.
2. Create `Modules/` dir, in the root dir.
3. Clone this repo into `Payments/` dir inside `/Modules/`.
4. Initialise `node_modules` by running `yarn` inside `/` of the InvoiceShelf repo.
5. Initialise `node_modules` by running `yarn` inside `/Modules/Payments/` of this repo.
6. Make the changes you wish to make.
7. Run `yarn build`, new `/Modules/Payments/dist/` dir will be created with built `css` and `js` bundles.
8. Upload the contents of `/Modules/Payments/` to your server with InvoiceShelf. Either use SFTP or zip up the folder.
9. Continue from [Installation (Step 2)](#installation)

## Troubleshooting

- If you get an empty popup when adding a new payment provider you need to clear your `cache`, `cookies`, and/or `site data` as specified in [Installation (Step 6)](#installation)

## Copyright

    Copyright (C) 2022-2023 Crater Invoice, Inc
    Copyright (C) 2024 <Rihards Simanovics> rihards.s@griffin-web.studio
