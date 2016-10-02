ALTER TABLE `ls_blog` ADD `is_deleted` bool DEFAULT FALSE;
ALTER TABLE `ls_topic` ADD `is_deleted` bool DEFAULT FALSE;
ALTER TABLE `ls_talk` ADD `is_deleted` bool DEFAULT FALSE;
ALTER TABLE `ls_user` ADD `is_deleted` bool DEFAULT FALSE;