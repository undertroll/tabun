ALTER TABLE `ls_blog` ADD `is_deleted` bool DEFAULT FALSE;
ALTER TABLE `ls_topic` ADD `is_deleted` bool DEFAULT FALSE;
ALTER TABLE `ls_topic_content` ADD `is_deleted` bool DEFAULT FALSE;
ALTER TABLE `ls_topic_tag` ADD `is_deleted` bool DEFAULT FALSE;
ALTER TABLE `ls_topic_read` ADD `is_deleted` bool DEFAULT FALSE;
ALTER TABLE `ls_talk` ADD `is_deleted` bool DEFAULT FALSE;
ALTER TABLE `ls_user` ADD `is_deleted` bool DEFAULT FALSE;