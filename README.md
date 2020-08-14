# Slack export parsing/rendering helper script

This PHP script generates HTML pages for Slack exports [converted to CSV format](https://github.com/lchski/slack-analysis). It uses the [Twig template library](https://twig.symfony.com/).

The CSV input files parsed by this script are generated from [Slack admin channel exports](https://slack.com/intl/en-ca/help/articles/204897248-Guide-to-Slack-import-and-export-tools) converted to CSV by [lchski](https://github.com/lchski)'s [slack-analysis](https://github.com/lchski/slack-analysis) tool.

## Installing / first-run

1. Clone the repository

2. In the repository folder, install the Twig composer package. You'll likely need to install Composer first using the [instructions here](https://getcomposer.org/download/).

When running `composer-setup.php`, you can use the following option to install it globally:

```
php composer-setup.php --install-dir=/usr/local/bin  --filename=composer
```

Then, run:

```
composer install
```

If that completes successfully, you're ready to go.

## Running

Confirm that the filepath in `index.php` lines up with where the CSV export is located.

Run the script with:

```
php index.php
```

The generated HTML pages are stored in the `output/` folder.

## Saving as Word docs

The most reliable way of converting the generated pages to Word format seems to be:

1. Open the generated HTML page in Chrome

2. Click "File > Save Page As…" and choose the "Webpage, Complete" option. Save the page somewhere on your computer (it will include an HTML page and a local folder containing any page images).

3. Use a local tool (e.g. Automator script) to rescale the images in the local folder down to a manageable size (e.g. max 300 pixels), since Word doesn't remember CSS-based image width and height values.

4. Open the saved "Webpage, Complete" option **in Microsoft Word**. Save the document as a PDF document (choose the "Best for printing" option to avoid images not displaying in the generated version). 
    * If saved as a "Word Document (.docx)", note that by default images are saved by external link rather than embedding, and so won't be displayed on other computers. [The steps here to embed linked images](https://superuser.com/questions/294978/transform-linked-images-to-embedded-images) can be used with smaller documents.
