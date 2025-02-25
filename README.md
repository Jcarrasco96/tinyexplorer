# TinyExplorer
I developed this system as a personal project to manage my home cloud, allowing me to quickly and easily access all my files from any internet-connected device. Although it was designed for personal use, its flexible structure makes it ideal for extending to business applications, such as a NAS system, offering an efficient solution for centralized data storage and access.

## Requirements
- PHP 8.2.12 or higher.
- Fileinfo, iconv, zip, tar and mbstring extensions are strongly recommended.
- MySQL

## Too much to do
- [x] Translations
- [x] Theme light and dark
- [x] Register users
- [x] Permissions to users
- [x] Database drivers for mysql, sqlsrv, pgsql, sqlite
- [ ] First installation step
- [ ] View file selected
- [ ] ...others
> There are still many things to do, but we continue to develop.

## Known issues
When I was testing on Debian using Proxmox and a NAS through Proxmox shared with the container I found permission problems, I could not create, rename or delete files or folders, or upload files to the server using the web system. I have been testing it on Windows and everything works fine. I will continue trying and looking for solutions to make it work correctly on Debian and through the NAS permissions.

## Database
### Table SYSTEM
```mysql
CREATE TABLE `system` (
  `id` int(11) NOT NULL,
  `theme` varchar(10) NOT NULL,
  `root_path` varchar(255) NOT NULL,
  `language` varchar(5) NOT NULL,
  `use_curl` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `system`
    ADD PRIMARY KEY (`id`);
```
### Table USER
```mysql
CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `info` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `user`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `username` (`username`) USING BTREE;
```

## Security
### For protect `database.sqlite` edit `.htaccess` file and append
```apacheconf
<Files database.sqlite>
    Require all denied
</Files>
```

## Images
![EXAMPLE 1](examples/1.png)
![EXAMPLE 2](examples/2.png)
> Images may be outdated