# [MageVision](https://www.magevision.com/) Display Filter Per Category Extension for Magento 2

## Overview
The Display Filter Per Category extension gives you the ability to define the filters you want to display in the layered navigation per category. 
Define the filters and their position for every category. Remove all filters from a category.

## Key Features
	* Define filters to display for every category
	* Define filter's position on every category
	* Remove all filters from a category
	* Better layered navigation filter management
    * Improve your customer's experience
	
## Other Features
	* Developed by a Magento Certified Developer
	* Meets Magento standard development practices
    * Single License is valid for 1 live Magento installation and unlimited test Magento installations
	* Simple installation
	* 100% open source

## Compatibility
Magento Community Edition 2.3
	
## Installing the Extension
	* Backup your web directory and store database
	* Download the extension
		1. Sign in to your account
		2. Navigate to menu My Account → My Downloads
		3. Find the extension and click to download it
	* Extract the downloaded ZIP file in a temporary directory
	* Upload the extracted folders and files of the extension to base (root) Magento directory. Do not replace the whole folders, but merge them.  If you have downloaded the extension from Magento Marketplace, then create the following folder path app/code/MageVision/CustomCarrierTrackers and upload there the extracted folders and files.
        * Connect via SSH to your Magento server as, or switch to, the Magento file system owner and run the following commands from the (root) Magento directory:
            1. cd path_to_the_magento_root_directory 
            2. php bin/magento maintenance:enable
            3. php bin/magento module:enable MageVision_DisplayFiltersPerCategory
            4. php bin/magento setup:upgrade
            5. php bin/magento setup:di:compile
            6. php bin/magento setup:static-content:deploy
            7. php bin/magento maintenance:disable
        * Log out from Magento admin and log in again
		
## How to Use

Activate the extension. 
Stores -> Configuration -> MageVision Extensions -> Display Filters Per Category

To configure Filters Per Category, navigate to the category page in admin and on tab Define Filters add the filters and their position
you want to display in layered navigation of that category. If extension is enabled and not filters were defined, then all filters are shown (default Magento's behaviour). 
In case Remove all filter is set to yes, then no filters are shown.

## Support
If you need support or have any questions directly related to a [MageVision](https://www.magevision.com/) extension, please contact us at [support@magevision.com](mailto:support@magevision.com)