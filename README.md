# Enterprise-Software-Engineering-DocStorageSystem
For my Fall 2022 class, I created a document storage system using phpmyadmin in order to move files pulled from my professor's server into my database hosted on a VirtualBox. 

For security reasons, I have replaced all the $password variables with empty strings. 

Notes:
- The php files in the apis folder is what I primarily used for calling to the api for creating the session, querying the files, pulling the files into my server, then uploading to the database.
- For the assignment I set up a cronjob that would run every file in the api folder in the order of:
api_create_session.php - creates the session_id 
api_query_files.php - queries the files for the session
api_fetch_files.php - fetches the filenames that were sent to the file_info database and downloads them into a folder on my server
api_movetoDB.php - moves the files from my server to the database
api_close_session.php - closes the session
clean.bash - cleans out the directory where I stored the files pulled from my professors site. 

every 30 minutes.

- reporting.php is what I used to make a report of my work. My data can be found in the report.pdf file
