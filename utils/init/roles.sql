USE monster;

DELETE FROM `roles`;

INSERT INTO `roles`(`id_role`, `role`) VALUES (0,'Admin');
INSERT INTO `roles`(`id_role`, `role`) VALUES (1,'User');
INSERT INTO `roles`(`id_role`, `role`) VALUES (2,'Contributor');