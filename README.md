# CardsD8

A configurable Drupal 8 module that creates a JSON feed used by a ReactJS app. The React app renders a search box and a list of content based on the content type chosen from the admin config page.

### Installation Instructions
1. Install and the CardsD8 module just like any other module.
2. Go to /admin/config/services/rtd-cards
3. Choose the content type you want to display, then click the Next button
3. Choose the text field you want to display for the description
4. If you want to display a background image for your content, choose the image field you want to use
5. Choose the taxonomy field you want to use for the searchable terms
6. Then click the "Save configuration" button
7. Go to the Admin -> Structure -> Blocks and place the RTD Cards Block into the desired region and configure block settings
8. Go to page(s) you added the RTD Cards Block and test