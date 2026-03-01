CREATE TABLE `roles` (
  `id` bigint PRIMARY KEY,
  `name` varchar(20) NOT NULL
);

CREATE TABLE `statuses` (
  `id` bigint PRIMARY KEY,
  `name` varchar(20) NOT NULL
);

CREATE TABLE `types` (
  `id` bigint PRIMARY KEY,
  `name` varchar(20) NOT NULL
);

CREATE TABLE `users` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255),
  `email` varchar(100) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` bigint,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `surveys` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `author_id` bigint NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(255),
  `status` bigint NOT NULL DEFAULT 1,
  `published_at` timestamp,
  `closed_at` timestamp,
  `created_at` timestamp DEFAULT (now()),
  `updated_at` timestamp
);

CREATE TABLE `questions` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `survey_id` bigint NOT NULL,
  `type` bigint NOT NULL,
  `text` varchar(255) NOT NULL,
  `order` bigint NOT NULL,
  `required` boolean DEFAULT false,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `options` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `question_id` bigint NOT NULL,
  `text` varchar(125) NOT NULL,
  `order` bigint NOT NULL
);

CREATE TABLE `responses` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `survey_id` bigint NOT NULL,
  `respondent_id` bigint NOT NULL,
  `completed_at` timestamp DEFAULT (now())
);

CREATE TABLE `answers` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `response_id` bigint NOT NULL,
  `question_id` bigint NOT NULL,
  `option_id` bigint,
  `text_value` varchar(255)
);

CREATE UNIQUE INDEX `unique_respondent_per_survey` ON `responses` (`survey_id`, `respondent_id`);

CREATE UNIQUE INDEX `one_answer_per_question` ON `answers` (`response_id`, `question_id`);

ALTER TABLE `users` ADD FOREIGN KEY (`role`) REFERENCES `roles` (`id`);

ALTER TABLE `surveys` ADD FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

ALTER TABLE `surveys` ADD FOREIGN KEY (`status`) REFERENCES `statuses` (`id`);

ALTER TABLE `questions` ADD FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`);

ALTER TABLE `questions` ADD FOREIGN KEY (`type`) REFERENCES `types` (`id`);

ALTER TABLE `options` ADD FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`);

ALTER TABLE `responses` ADD FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`);

ALTER TABLE `responses` ADD FOREIGN KEY (`respondent_id`) REFERENCES `users` (`id`);

ALTER TABLE `answers` ADD FOREIGN KEY (`response_id`) REFERENCES `responses` (`id`);

ALTER TABLE `answers` ADD FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`);

ALTER TABLE `answers` ADD FOREIGN KEY (`option_id`) REFERENCES `options` (`id`);
