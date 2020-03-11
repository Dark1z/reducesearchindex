# Reduce Search Index [RSI]
   
This Extension Lets Admin to control Search Index .  😉   
   
   
#### Status Badge for Reduce Search Index [RSI] on phpBB v3.3.x :   
![Travis-CI](https://img.shields.io/badge/Travis-CI-8000FF.svg) : [![Build Status](https://travis-ci.com/dark-1/reducesearchindex.svg?branch=master)](https://travis-ci.com/dark-1/reducesearchindex)   
![Scrutinizer-CI](https://img.shields.io/badge/Scrutinizer-CI-8000FF.svg) : [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dark-1/reducesearchindex/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dark-1/reducesearchindex/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/dark-1/reducesearchindex/badges/build.png?b=master)](https://scrutinizer-ci.com/g/dark-1/reducesearchindex/build-status/master)   
![Actions-CI](https://img.shields.io/badge/Actions-CI-8000FF.svg) : [![Actions Status](https://github.com/dark-1/reducesearchindex/workflows/Actions%20CI/badge.svg)](https://github.com/dark-1/reducesearchindex/actions?workflow=Actions%20CI)   
![Shields.IO](https://img.shields.io/badge/Shields-IO-8000FF.svg?style=flat-square) : [![GitHub release](https://img.shields.io/github/release/dark-1/reducesearchindex.svg?style=flat-square) ![license](https://img.shields.io/github/license/dark-1/reducesearchindex.svg?style=flat-square) ![Github All Releases](https://img.shields.io/github/downloads/dark-1/reducesearchindex/total.svg?style=flat-square) ![HitCount](http://hits.dwyl.io/dark-1/reducesearchindex.svg)](https://github.com/dark-1/reducesearchindex)   
   
   
## **Features :**   
   
1. Option to Enable/Disable this extension.   
2. Option to set the Time from which Search Index is Kept, Also Time before which Search Index is Deleted.   
3. Option to set Interval to Update the above Time when Cron is run.   
4. Option to `Topic + Lock` / `Topic Only` / `Post Only` / `Disable` for each Forum.   
    - `Topic + Lock` : Search Index from the Forum for the Topic is Deleted and Locked as per the above Time.   
    - `Topic Only` : Search Index from the Forum for the Topic is Deleted as per the above Time.   
    - `Post Only` : Search Index from the Forum for the Post is Deleted as per the above Time.   
5. Option to Enable/Disable the Cron task to Reduce Search Index.   
6. Option to set Interval for Cron task.   
7. Option to set the Last Run Time for Cron task.   
8. Option to Run the Cron task.   
9. Display Reduced Notice on Search Page.   
   
   
## **Notes :**   
   
1. **phpBB Native Fulltext** `fulltext_native` : This is Fully Supported.   
2. **MySQL Fulltext** `fulltext_mysql` : This can not be Supported due fully DataBase side implementation & management.   
3. **PostgreSQL Fulltext** `fulltext_postgres` : This can not be Supported due fully DataBase side implementation & management.   
4. **Sphinx Fulltext** `fulltext_sphinx` : This is not currently Supported because I myself do not know if it's supported or not,
also if it's supported then *"how to ???"* , If somebody is able to help me with this then I'll be very happy, credits for the same will be given.   
   
   
## For More Detail's & ScreenShot's : [GitHub Page](https://dark-1.github.io/reducesearchindex/)   
   
   
## **Installation :**   
   
1. Download and unzip the Latest release.   
2. Copy the `dark1/reducesearchindex` folder to `/ext/dark1/reducesearchindex`.   
3. Navigate in the `ACP` to `Customise` -> `Manage extensions`.   
4. Look for `Reduce Search Index` under the `Disabled Extensions` list, and click the `Enable` link.   
5. Set up and configure the `Reduce Search Index` extension by navigating in the `ACP` to `Extensions` -> `Reduce Search Index [RSI]`.   
6. If required Purge the cache in `ACP` & also if required then in your Browser.   
7. D0Ne !!! EnJ0Y  😃   
   
Detailed phpBB standard Installation of Extensions here : [phpBB Extensions Installing](https://www.phpbb.com/extensions/installing/#installing)   
   
   
## **Updation :**   
   
1. Navigate in the `ACP` to `Customise` -> `Manage extensions`.   
2. Look for `Reduce Search Index` under the `Enabled Extensions` list, and click the `Disable` link.   
3. Delete the Files from the `dark1/reducesearchindex` folder at `/ext/dark1/reducesearchindex`.   
4. Download and unzip the new Latest release Files.   
5. Copy the new Latest release Files from the `dark1/reducesearchindex` folder to `/ext/dark1/reducesearchindex`.   
6. Look for `Reduce Search Index` under the `Disabled Extensions` list, and click the `Enable` link.   
7. D0Ne !!! EnJ0Y  😃   
   
Detailed phpBB standard Updation of Extensions here : [phpBB Extensions Updating](https://www.phpbb.com/extensions/installing/#updating)   
   
   
## **Uninstallation :**   
   
1. Navigate in the `ACP` to `Customise` -> `Manage extensions`.   
2. Look for `Reduce Search Index` under the `Enabled Extensions` list, and click the `Disable` link.   
3. If want to Fully Uninstall then Look for `Reduce Search Index` under the `Disabled Extensions` list, and click the `Delete data` link.   
4. At this point you can re-enable the extension, it will be as if it were being installed for the first time.   
5. If want to Remove the Files then delete the `dark1/reducesearchindex` folder from `/ext/dark1/reducesearchindex`.   
6. If required Purge the cache in `ACP` & also if required then in your Browser.   
7. D0Ne !!! EnJ0Y  😃   
   
Detailed phpBB standard Uninstallation of Extensions here : [phpBB Extensions Removing](https://www.phpbb.com/extensions/installing/#removing)   
   
   
## **Links :**   
   
**GitHub Repository** : [reducesearchindex](https://github.com/dark-1/reducesearchindex)   
**phpBB Customisation Database Extension** : [Reduce Search Index](https://www.phpbb.com/customise/db/extension/reduce_search_index)   
**For more Details Go Here** : [Reduce Search Index [RSI]](https://dark-1.github.io/reducesearchindex)   
   
**Credit where credit's due** :   
Date & Time Picker : **Any+Time** by *Andrew M. Andrews III* at [ama3.com/anytime](https://www.ama3.com/anytime),   
which can be found at `dark1/reducesearchindex/styles/all/template/DateTimePicker`   
   
   
## License [GPLv2](license.txt)   
   
--------------   
EnJoY  😃   
Best Regards.  👍   
   
