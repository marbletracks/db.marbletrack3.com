#!/bin/bash

./send_files_to_chatGPT.sh classes/Database/*.php > Database_classes.php
./send_files_to_chatGPT.sh classes/Domain/*.php > Domain_classes.php
./send_files_to_chatGPT.sh classes/Media/*.php > Media_classes.php
./send_files_to_chatGPT.sh classes/Physical/*.php > Physical_classes.php
./send_files_to_chatGPT.sh db_schemas/*/* > db_schemas.sql
./send_files_to_chatGPT.sh wwwroot/admin/*/*/* > wwwroot_admin_files.php
./send_files_to_chatGPT.sh templates/admin/*/*/* > templates_admin_files.php

