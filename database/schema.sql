/*M!999999\- enable the sandbox mode */ 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_learning_feedback` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `time_entry_id` bigint(20) unsigned NOT NULL,
  `original_description` text DEFAULT NULL,
  `ai_description` text DEFAULT NULL,
  `feedback` enum('accepted','rejected','modified') DEFAULT 'accepted',
  `modified_description` text DEFAULT NULL,
  `feedback_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_time_entry_id` (`time_entry_id`),
  CONSTRAINT `ai_learning_feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ai_learning_feedback_ibfk_2` FOREIGN KEY (`time_entry_id`) REFERENCES `time_entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `openai_api_key` varchar(255) DEFAULT NULL,
  `openai_model` varchar(255) NOT NULL DEFAULT 'gpt-4o-mini',
  `openai_temperature` decimal(3,2) NOT NULL DEFAULT 0.70,
  `openai_max_tokens` int(11) NOT NULL DEFAULT 2000,
  `anthropic_api_key` varchar(255) DEFAULT NULL,
  `anthropic_model` varchar(255) NOT NULL DEFAULT 'claude-3-5-sonnet-20241022',
  `anthropic_temperature` decimal(3,2) NOT NULL DEFAULT 0.70,
  `anthropic_max_tokens` int(11) NOT NULL DEFAULT 2000,
  `default_provider` enum('openai','anthropic') NOT NULL DEFAULT 'openai',
  `ai_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `log_ai_usage` tinyint(1) NOT NULL DEFAULT 1,
  `show_ai_costs` tinyint(1) NOT NULL DEFAULT 1,
  `ai_chat_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `ai_task_generator_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `ai_time_predictions_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `ai_invoice_generation_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `ai_digest_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `ai_learning_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `ai_time_entry_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `ai_chat_system_prompt` text DEFAULT NULL,
  `ai_chat_max_tokens` int(11) NOT NULL DEFAULT 2000,
  `ai_chat_temperature` decimal(3,2) NOT NULL DEFAULT 0.70,
  `ai_chat_history_limit` int(11) NOT NULL DEFAULT 20,
  `ai_chat_show_context` tinyint(1) NOT NULL DEFAULT 1,
  `ai_chat_allow_file_analysis` tinyint(1) NOT NULL DEFAULT 0,
  `ai_chat_quick_actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ai_chat_quick_actions`)),
  `ai_chat_welcome_message` varchar(500) DEFAULT NULL,
  `openai_input_cost_per_1k` decimal(10,6) NOT NULL DEFAULT 0.000150,
  `openai_output_cost_per_1k` decimal(10,6) NOT NULL DEFAULT 0.000600,
  `anthropic_input_cost_per_1k` decimal(10,6) NOT NULL DEFAULT 0.003000,
  `anthropic_output_cost_per_1k` decimal(10,6) NOT NULL DEFAULT 0.015000,
  `max_requests_per_minute` int(11) NOT NULL DEFAULT 60,
  `max_tokens_per_day` int(11) NOT NULL DEFAULT 100000,
  `max_cost_per_month` int(11) NOT NULL DEFAULT 100,
  `custom_prompts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_prompts`)),
  `model_overrides` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`model_overrides`)),
  `proxy_url` varchar(255) DEFAULT NULL,
  `timeout_seconds` int(11) NOT NULL DEFAULT 30,
  `total_requests_today` int(11) NOT NULL DEFAULT 0,
  `total_tokens_today` int(11) NOT NULL DEFAULT 0,
  `total_cost_this_month` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `last_reset_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ai_time_entry_default_rules` text DEFAULT NULL COMMENT 'Default naming rules for all projects',
  `ai_time_entry_default_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Default task categories' CHECK (json_valid(`ai_time_entry_default_categories`)),
  `ai_time_entry_example_patterns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Default good naming examples' CHECK (json_valid(`ai_time_entry_example_patterns`)),
  `ai_time_entry_prompt_template` text DEFAULT NULL COMMENT 'Global prompt template for time entry improvement',
  `ai_time_entry_max_length` int(11) NOT NULL DEFAULT 100 COMMENT 'Maximum character length for descriptions',
  `ai_time_entry_auto_improve` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Automatically improve descriptions on save',
  `ai_time_entry_learn_from_history` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Learn from recent time entries',
  `ai_time_entry_history_days` int(11) NOT NULL DEFAULT 30 COMMENT 'Days of history to consider for learning',
  `ai_invoice_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `ai_invoice_system_prompt` text DEFAULT NULL,
  `ai_invoice_consolidation_instructions` text DEFAULT NULL,
  `ai_invoice_description_prompt` text DEFAULT NULL,
  `ai_invoice_output_language` varchar(10) NOT NULL DEFAULT 'nl',
  `ai_invoice_max_description_words` int(11) NOT NULL DEFAULT 100,
  `ai_invoice_include_technical_details` tinyint(1) NOT NULL DEFAULT 1,
  `ai_invoice_group_similar_threshold` decimal(3,2) NOT NULL DEFAULT 0.80,
  `ai_invoice_bundle_press_releases` tinyint(1) NOT NULL DEFAULT 1,
  `ai_invoice_list_all_media` tinyint(1) NOT NULL DEFAULT 1,
  `ai_invoice_group_by_activity_type` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_usage_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service` varchar(255) NOT NULL DEFAULT 'claude',
  `model` varchar(255) NOT NULL,
  `feature` varchar(255) NOT NULL,
  `prompt` text DEFAULT NULL,
  `prompt_tokens` int(11) NOT NULL DEFAULT 0,
  `response_tokens` int(11) NOT NULL DEFAULT 0,
  `total_tokens` int(11) NOT NULL DEFAULT 0,
  `estimated_cost` decimal(10,6) NOT NULL DEFAULT 0.000000,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `cached_response` tinyint(1) NOT NULL DEFAULT 0,
  `response_time_ms` int(11) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'success',
  `error_message` text DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_usage_logs_project_id_foreign` (`project_id`),
  KEY `ai_usage_logs_service_model_index` (`service`,`model`),
  KEY `ai_usage_logs_feature_index` (`feature`),
  KEY `ai_usage_logs_created_at_index` (`created_at`),
  KEY `ai_usage_logs_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `ai_usage_logs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ai_usage_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `archived_project_subtasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `original_id` bigint(20) unsigned NOT NULL,
  `project_task_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `fee_type` enum('in_fee','extended') NOT NULL DEFAULT 'in_fee',
  `pricing_type` enum('hourly_rate','fixed_price') NOT NULL DEFAULT 'hourly_rate',
  `fixed_price` decimal(10,2) DEFAULT NULL,
  `hourly_rate_override` decimal(8,2) DEFAULT NULL,
  `estimated_hours` decimal(8,2) DEFAULT NULL,
  `source_type` varchar(255) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `is_service_item` tinyint(1) NOT NULL DEFAULT 0,
  `service_name` varchar(255) DEFAULT NULL,
  `service_color` varchar(7) DEFAULT NULL,
  `original_service_id` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `archived_project_subtasks_project_task_id_index` (`project_task_id`),
  KEY `archived_project_subtasks_original_id_index` (`original_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `calendar_event_id` bigint(20) unsigned DEFAULT NULL,
  `action` enum('created','updated','deleted','converted','cancelled','synced','attendee_added','attendee_removed','attendee_responded') NOT NULL,
  `description` varchar(255) NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `calendar_activities_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `calendar_activities_calendar_event_id_created_at_index` (`calendar_event_id`,`created_at`),
  CONSTRAINT `calendar_activities_calendar_event_id_foreign` FOREIGN KEY (`calendar_event_id`) REFERENCES `calendar_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `calendar_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `provider_type` enum('microsoft','google','apple') NOT NULL DEFAULT 'microsoft',
  `external_event_id` varchar(255) DEFAULT NULL,
  `ms_event_id` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `timezone` varchar(255) NOT NULL DEFAULT 'Europe/Amsterdam',
  `is_all_day` tinyint(1) NOT NULL DEFAULT 0,
  `location` varchar(255) DEFAULT NULL,
  `attendees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attendees`)),
  `categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`categories`)),
  `organizer_email` varchar(255) DEFAULT NULL,
  `organizer_name` varchar(255) DEFAULT NULL,
  `is_converted` tinyint(1) NOT NULL DEFAULT 0,
  `time_entry_id` bigint(20) unsigned DEFAULT NULL,
  `ms_raw_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ms_raw_data`)),
  `provider_raw_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_raw_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `calendar_events_ms_event_id_unique` (`ms_event_id`),
  KEY `calendar_events_time_entry_id_foreign` (`time_entry_id`),
  KEY `calendar_events_user_id_start_datetime_index` (`user_id`,`start_datetime`),
  KEY `calendar_events_is_converted_index` (`is_converted`),
  KEY `calendar_events_provider_type_external_event_id_index` (`provider_type`,`external_event_id`),
  KEY `calendar_events_user_id_provider_type_index` (`user_id`,`provider_type`),
  CONSTRAINT `calendar_events_time_entry_id_foreign` FOREIGN KEY (`time_entry_id`) REFERENCES `time_entries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `calendar_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar_sync_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `provider_type` enum('microsoft','google','apple') NOT NULL DEFAULT 'microsoft',
  `sync_type` enum('manual','automatic','webhook','full') NOT NULL,
  `status` enum('started','completed','failed','error','success') NOT NULL,
  `events_synced` int(11) NOT NULL DEFAULT 0,
  `events_created` int(11) NOT NULL DEFAULT 0,
  `events_updated` int(11) NOT NULL DEFAULT 0,
  `events_deleted` int(11) NOT NULL DEFAULT 0,
  `events_failed` int(11) NOT NULL DEFAULT 0,
  `sync_from` datetime DEFAULT NULL,
  `sync_to` datetime DEFAULT NULL,
  `sync_started_at` datetime DEFAULT NULL,
  `sync_completed_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `calendar_sync_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `calendar_sync_logs_status_index` (`status`),
  KEY `calendar_sync_logs_user_id_sync_completed_at_index` (`user_id`,`sync_completed_at`),
  KEY `calendar_sync_logs_user_id_provider_type_index` (`user_id`,`provider_type`),
  CONSTRAINT `calendar_sync_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `legal_name` varchar(255) DEFAULT NULL,
  `vat_number` varchar(255) DEFAULT NULL,
  `registration_number` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `house_number` varchar(20) DEFAULT NULL,
  `addition` varchar(20) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) NOT NULL DEFAULT 'Netherlands',
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `default_hourly_rate` decimal(8,2) NOT NULL DEFAULT 75.00,
  `default_fixed_price` decimal(10,2) DEFAULT NULL,
  `invoice_prefix` varchar(10) DEFAULT NULL,
  `next_invoice_number` int(11) NOT NULL DEFAULT 1,
  `is_main_invoicing` tinyint(1) NOT NULL DEFAULT 0,
  `bank_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`bank_details`)),
  `invoice_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`invoice_settings`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `companies_name_is_active_index` (`name`,`is_active`),
  KEY `companies_is_main_invoicing_index` (`is_main_invoicing`),
  KEY `companies_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_activities_user_id_foreign` (`user_id`),
  KEY `company_activities_company_id_created_at_index` (`company_id`,`created_at`),
  CONSTRAINT `company_activities_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `company_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_plugins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `plugin_id` bigint(20) unsigned NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_plugins_company_id_plugin_id_unique` (`company_id`,`plugin_id`),
  KEY `company_plugins_plugin_id_foreign` (`plugin_id`),
  KEY `company_plugins_is_enabled_index` (`is_enabled`),
  CONSTRAINT `company_plugins_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `company_plugins_plugin_id_foreign` FOREIGN KEY (`plugin_id`) REFERENCES `plugins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_activities_user_id_foreign` (`user_id`),
  KEY `contact_activities_contact_id_created_at_index` (`contact_id`,`created_at`),
  CONSTRAINT `contact_activities_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contact_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `role` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contact_companies_contact_id_company_id_unique` (`contact_id`,`company_id`),
  KEY `contact_companies_company_id_foreign` (`company_id`),
  KEY `contact_companies_contact_id_is_primary_index` (`contact_id`,`is_primary`),
  CONSTRAINT `contact_companies_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contact_companies_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contacts_customer_id_is_primary_index` (`customer_id`,`is_primary`),
  KEY `contacts_company_id_index` (`company_id`),
  CONSTRAINT `contacts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contacts_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_activities_user_id_foreign` (`user_id`),
  KEY `customer_activities_customer_id_created_at_index` (`customer_id`,`created_at`),
  CONSTRAINT `customer_activities_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Is this the primary managing company',
  `role` varchar(255) DEFAULT NULL COMMENT 'Role of company for this customer',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_companies_customer_id_company_id_unique` (`customer_id`,`company_id`),
  KEY `customer_companies_company_id_foreign` (`company_id`),
  KEY `customer_companies_customer_id_is_primary_index` (`customer_id`,`is_primary`),
  CONSTRAINT `customer_companies_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_companies_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `addition` varchar(50) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Netherlands',
  `contact_person` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `invoice_template_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_name_is_active_index` (`name`,`is_active`),
  KEY `customers_company_index` (`company`),
  KEY `customers_company_id_index` (`company_id`),
  KEY `customers_invoice_template_id_foreign` (`invoice_template_id`),
  CONSTRAINT `customers_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customers_invoice_template_id_foreign` FOREIGN KEY (`invoice_template_id`) REFERENCES `invoice_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_draft_actions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `action` enum('created','line_added','line_removed','line_merged','description_changed','amount_adjusted','finalized') NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_draft_actions_invoice_id_created_at_index` (`invoice_id`,`created_at`),
  KEY `invoice_draft_actions_user_id_action_index` (`user_id`,`action`),
  CONSTRAINT `invoice_draft_actions_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  CONSTRAINT `invoice_draft_actions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `project_monthly_fee_id` bigint(20) unsigned DEFAULT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `group_milestone_id` bigint(20) unsigned DEFAULT NULL,
  `group_task_id` bigint(20) unsigned DEFAULT NULL,
  `group_subtask_id` bigint(20) unsigned DEFAULT NULL,
  `line_type` enum('hours','milestone','service','adjustment','custom','budget_adjustment') NOT NULL,
  `fee_type` enum('in_fee','additional') NOT NULL DEFAULT 'in_fee',
  `is_ai_generated` tinyint(1) NOT NULL DEFAULT 0,
  `original_description` text DEFAULT NULL,
  `activity_group` varchar(255) DEFAULT NULL,
  `consolidated_count` int(11) NOT NULL DEFAULT 1,
  `description` text NOT NULL,
  `detailed_description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(50) NOT NULL DEFAULT 'hours',
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `category` varchar(50) NOT NULL DEFAULT 'work',
  `is_billable` tinyint(1) NOT NULL DEFAULT 1,
  `defer_to_next_month` tinyint(1) NOT NULL DEFAULT 0,
  `is_service_package` tinyint(1) NOT NULL DEFAULT 0,
  `service_id` bigint(20) unsigned DEFAULT NULL,
  `service_color` varchar(50) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `unit_price_ex_vat` decimal(12,2) DEFAULT 0.00,
  `fee_capped_amount` decimal(12,2) DEFAULT NULL,
  `original_amount` decimal(12,2) DEFAULT NULL,
  `vat_rate` decimal(5,2) DEFAULT 21.00,
  `line_total_ex_vat` decimal(12,2) DEFAULT 0.00,
  `line_vat_amount` decimal(12,2) DEFAULT 0.00,
  `is_merged_line` tinyint(1) NOT NULL DEFAULT 0,
  `source_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`source_data`)),
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_lines_project_monthly_fee_id_foreign` (`project_monthly_fee_id`),
  KEY `invoice_lines_invoice_id_sort_order_index` (`invoice_id`,`sort_order`),
  KEY `invoice_lines_line_type_project_monthly_fee_id_index` (`line_type`,`project_monthly_fee_id`),
  KEY `invoice_lines_fee_type_index` (`fee_type`),
  KEY `invoice_lines_is_ai_generated_index` (`is_ai_generated`),
  KEY `invoice_lines_activity_group_index` (`activity_group`),
  CONSTRAINT `invoice_lines_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_lines_project_monthly_fee_id_foreign` FOREIGN KEY (`project_monthly_fee_id`) REFERENCES `project_monthly_fees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=244 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `template_type` varchar(255) NOT NULL DEFAULT 'standard',
  `color_scheme` varchar(255) NOT NULL DEFAULT 'blue',
  `primary_color` varchar(7) DEFAULT NULL,
  `secondary_color` varchar(7) DEFAULT NULL,
  `accent_color` varchar(7) DEFAULT NULL,
  `logo_position` varchar(255) NOT NULL DEFAULT 'left',
  `logo_path` varchar(255) DEFAULT NULL,
  `show_logo` tinyint(1) NOT NULL DEFAULT 1,
  `show_header` tinyint(1) NOT NULL DEFAULT 1,
  `show_payment_terms` tinyint(1) NOT NULL DEFAULT 1,
  `show_bank_details` tinyint(1) NOT NULL DEFAULT 1,
  `show_budget_overview` tinyint(1) NOT NULL DEFAULT 1,
  `show_additional_costs_section` tinyint(1) NOT NULL DEFAULT 1,
  `show_project_details` tinyint(1) NOT NULL DEFAULT 1,
  `show_time_entry_details` tinyint(1) NOT NULL DEFAULT 0,
  `show_page_numbers` tinyint(1) NOT NULL DEFAULT 1,
  `show_footer` tinyint(1) NOT NULL DEFAULT 1,
  `show_subtotals` tinyint(1) NOT NULL DEFAULT 1,
  `show_tax_details` tinyint(1) NOT NULL DEFAULT 1,
  `show_discount_section` tinyint(1) NOT NULL DEFAULT 0,
  `show_notes_section` tinyint(1) NOT NULL DEFAULT 1,
  `header_content` text DEFAULT NULL,
  `footer_content` text DEFAULT NULL,
  `payment_terms_text` text DEFAULT NULL,
  `font_family` varchar(255) NOT NULL DEFAULT 'Inter',
  `font_size` varchar(255) NOT NULL DEFAULT 'normal',
  `line_spacing` varchar(255) NOT NULL DEFAULT 'normal',
  `blade_template` varchar(255) NOT NULL DEFAULT 'invoices.templates.standard',
  `block_positions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`block_positions`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `custom_html` longtext DEFAULT NULL,
  `custom_css` longtext DEFAULT NULL,
  `editor_mode` enum('visual','code') NOT NULL DEFAULT 'visual',
  `use_custom_code` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_templates_slug_unique` (`slug`),
  KEY `invoice_templates_company_id_is_active_index` (`company_id`,`is_active`),
  CONSTRAINT `invoice_templates_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `invoicing_company_id` bigint(20) unsigned NOT NULL,
  `invoice_template_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `invoice_number` varchar(255) DEFAULT NULL,
  `status` enum('draft','finalized','sent','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
  `billing_type` varchar(50) NOT NULL DEFAULT 'monthly',
  `is_editable` tinyint(1) NOT NULL DEFAULT 1,
  `draft_name` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 21.00,
  `previous_month_remaining` decimal(12,2) NOT NULL DEFAULT 0.00,
  `monthly_budget` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_budget` decimal(12,2) NOT NULL DEFAULT 0.00,
  `next_month_rollover` decimal(12,2) NOT NULL DEFAULT 0.00,
  `work_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `service_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `additional_costs` decimal(12,2) NOT NULL DEFAULT 0.00,
  `additional_costs_in_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `additional_costs_outside_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal_ex_vat` decimal(12,2) NOT NULL DEFAULT 0.00,
  `vat_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_inc_vat` decimal(12,2) NOT NULL DEFAULT 0.00,
  `finalized_by` bigint(20) unsigned DEFAULT NULL,
  `finalized_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `paid_amount` decimal(12,2) DEFAULT NULL,
  `ai_generated` tinyint(1) NOT NULL DEFAULT 0,
  `ai_confidence_score` decimal(3,2) DEFAULT NULL,
  `ai_generated_at` timestamp NULL DEFAULT NULL,
  `activity_report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`activity_report_data`)),
  `fee_balance_previous` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fee_balance_current` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fee_performed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fee_balance_new` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoices_finalized_by_foreign` (`finalized_by`),
  KEY `invoices_project_id_status_index` (`project_id`,`status`),
  KEY `invoices_invoicing_company_id_status_index` (`invoicing_company_id`,`status`),
  KEY `invoices_customer_id_invoice_date_index` (`customer_id`,`invoice_date`),
  KEY `invoices_invoice_number_index` (`invoice_number`),
  KEY `invoices_created_by_foreign` (`created_by`),
  KEY `invoices_invoice_template_id_foreign` (`invoice_template_id`),
  KEY `invoices_ai_generated_index` (`ai_generated`),
  KEY `invoices_project_id_period_start_period_end_index` (`project_id`,`period_start`,`period_end`),
  CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `invoices_finalized_by_foreign` FOREIGN KEY (`finalized_by`) REFERENCES `users` (`id`),
  CONSTRAINT `invoices_invoice_template_id_foreign` FOREIGN KEY (`invoice_template_id`) REFERENCES `invoice_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_invoicing_company_id_foreign` FOREIGN KEY (`invoicing_company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `invoices_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=1936 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_ai_analysis_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `feed_item_id` bigint(20) unsigned DEFAULT NULL,
  `ai_provider` varchar(255) NOT NULL,
  `tokens_used` int(11) NOT NULL DEFAULT 0,
  `cost` decimal(8,6) NOT NULL DEFAULT 0.000000,
  `processing_time_ms` int(11) NOT NULL DEFAULT 0,
  `matched_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`matched_keywords`)),
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_ai_analysis_logs_feed_item_id_foreign` (`feed_item_id`),
  KEY `media_ai_analysis_logs_created_at_index` (`created_at`),
  CONSTRAINT `media_ai_analysis_logs_feed_item_id_foreign` FOREIGN KEY (`feed_item_id`) REFERENCES `rss_feed_cache` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_sources` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `rss_url` text NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'general',
  `language` varchar(5) NOT NULL DEFAULT 'nl',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `check_frequency` int(11) NOT NULL DEFAULT 30,
  `last_checked_at` timestamp NULL DEFAULT NULL,
  `reliability_score` int(11) NOT NULL DEFAULT 100,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_sources_is_active_last_checked_at_index` (`is_active`,`last_checked_at`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `monthly_intercompany_charges` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `from_company_id` bigint(20) unsigned NOT NULL,
  `to_company_id` bigint(20) unsigned NOT NULL,
  `year` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `billing_method` enum('hourly_rate','fixed_monthly') NOT NULL,
  `agreed_amount` decimal(12,2) NOT NULL,
  `actual_hours_worked` decimal(8,2) NOT NULL DEFAULT 0.00,
  `actual_hours_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `amount_to_charge` decimal(12,2) NOT NULL,
  `status` enum('draft','approved','invoiced','paid') NOT NULL DEFAULT 'draft',
  `invoice_number` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_monthly_charge` (`project_id`,`from_company_id`,`to_company_id`,`year`,`month`),
  KEY `monthly_intercompany_charges_to_company_id_foreign` (`to_company_id`),
  KEY `monthly_intercompany_charges_project_id_year_month_index` (`project_id`,`year`,`month`),
  KEY `monthly_intercompany_charges_from_company_id_status_index` (`from_company_id`,`status`),
  CONSTRAINT `monthly_intercompany_charges_from_company_id_foreign` FOREIGN KEY (`from_company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `monthly_intercompany_charges_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  CONSTRAINT `monthly_intercompany_charges_to_company_id_foreign` FOREIGN KEY (`to_company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ms_graph_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text DEFAULT NULL,
  `expires` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_additional_costs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cost_type` enum('one_time','monthly_recurring') NOT NULL,
  `fee_type` enum('in_fee','additional') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `category` enum('hosting','software','licenses','services','other') NOT NULL DEFAULT 'other',
  `vendor` varchar(255) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `auto_invoice` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_additional_costs_created_by_foreign` (`created_by`),
  KEY `project_additional_costs_project_id_is_active_index` (`project_id`,`is_active`),
  KEY `project_additional_costs_project_id_cost_type_start_date_index` (`project_id`,`cost_type`,`start_date`),
  KEY `project_additional_costs_category_is_active_index` (`category`,`is_active`),
  CONSTRAINT `project_additional_costs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `project_additional_costs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `billing_method` enum('fixed_amount','actual_hours') NOT NULL DEFAULT 'actual_hours',
  `role` enum('main_invoicing','subcontractor') NOT NULL DEFAULT 'subcontractor',
  `fixed_amount` decimal(10,2) DEFAULT NULL,
  `hourly_rate` decimal(8,2) DEFAULT NULL,
  `hourly_rate_override` decimal(8,2) DEFAULT NULL,
  `monthly_fixed_amount` decimal(10,2) DEFAULT NULL,
  `billing_start_date` date DEFAULT NULL,
  `billing_end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_companies_project_id_company_id_unique` (`project_id`,`company_id`),
  KEY `project_companies_company_id_foreign` (`company_id`),
  KEY `project_companies_project_id_role_index` (`project_id`,`role`),
  KEY `project_companies_project_id_is_active_index` (`project_id`,`is_active`),
  CONSTRAINT `project_companies_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_companies_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_media_campaigns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `press_release_date` date NOT NULL,
  `campaign_type` enum('product_launch','feature_announcement','company_news','event','partnership','other') NOT NULL DEFAULT 'other',
  `target_audience` varchar(255) DEFAULT NULL,
  `expected_reach` int(11) DEFAULT NULL,
  `actual_reach` int(11) DEFAULT NULL,
  `keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`keywords`)),
  `status` enum('planning','active','completed','on_hold') NOT NULL DEFAULT 'planning',
  `budget` decimal(10,2) DEFAULT NULL,
  `actual_cost` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_media_campaigns_project_id_status_index` (`project_id`,`status`),
  KEY `project_media_campaigns_press_release_date_index` (`press_release_date`),
  KEY `project_media_campaigns_parent_id_foreign` (`parent_id`),
  CONSTRAINT `project_media_campaigns_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `project_media_campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_media_campaigns_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_media_mentions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned DEFAULT NULL,
  `user_media_mention_id` bigint(20) unsigned NOT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `assignment_method` enum('automatic','manual','ai_suggested') NOT NULL DEFAULT 'manual',
  `confidence_score` int(11) NOT NULL DEFAULT 100,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_media_mentions_project_id_user_media_mention_id_unique` (`project_id`,`user_media_mention_id`),
  KEY `project_media_mentions_user_media_mention_id_foreign` (`user_media_mention_id`),
  KEY `project_media_mentions_campaign_id_created_at_index` (`campaign_id`,`created_at`),
  KEY `project_media_mentions_assigned_by_foreign` (`assigned_by`),
  CONSTRAINT `project_media_mentions_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_media_mentions_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `project_media_campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_media_mentions_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_media_mentions_user_media_mention_id_foreign` FOREIGN KEY (`user_media_mention_id`) REFERENCES `user_media_mentions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_milestones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','in_progress','completed','on_hold') NOT NULL DEFAULT 'pending',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `fee_type` enum('in_fee','extended') NOT NULL DEFAULT 'in_fee',
  `pricing_type` enum('fixed_price','hourly_rate') NOT NULL DEFAULT 'hourly_rate',
  `fixed_price` decimal(10,2) DEFAULT NULL,
  `hourly_rate_override` decimal(8,2) DEFAULT NULL,
  `estimated_hours` decimal(8,2) DEFAULT NULL,
  `invoicing_trigger` enum('completion','approval','delivery') NOT NULL DEFAULT 'completion',
  `source_type` enum('manual','template','service') DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `is_service_item` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Indicates if this milestone was imported from a service',
  `service_name` varchar(255) DEFAULT NULL COMMENT 'Custom service name (e.g. Webdesign example.com)',
  `service_color` varchar(7) DEFAULT '#3B82F6' COMMENT 'Color for visual identification of service items',
  `original_service_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Reference to original service',
  `deliverables` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_milestones_project_id_sort_order_index` (`project_id`,`sort_order`),
  KEY `project_milestones_project_id_status_index` (`project_id`,`status`),
  KEY `project_milestones_source_type_source_id_index` (`source_type`,`source_id`),
  CONSTRAINT `project_milestones_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_monthly_additional_costs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `additional_cost_id` bigint(20) unsigned NOT NULL,
  `year` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `fee_type` enum('in_fee','additional') NOT NULL,
  `prorated_amount` decimal(10,2) NOT NULL,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_line_id` bigint(20) unsigned DEFAULT NULL,
  `is_invoiced` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_monthly_cost` (`additional_cost_id`,`year`,`month`),
  KEY `project_monthly_additional_costs_project_id_year_month_index` (`project_id`,`year`,`month`),
  KEY `project_monthly_additional_costs_invoice_line_id_foreign` (`invoice_line_id`),
  KEY `project_monthly_additional_costs_invoice_id_is_invoiced_index` (`invoice_id`,`is_invoiced`),
  CONSTRAINT `project_monthly_additional_costs_additional_cost_id_foreign` FOREIGN KEY (`additional_cost_id`) REFERENCES `project_additional_costs` (`id`),
  CONSTRAINT `project_monthly_additional_costs_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  CONSTRAINT `project_monthly_additional_costs_invoice_line_id_foreign` FOREIGN KEY (`invoice_line_id`) REFERENCES `invoice_lines` (`id`),
  CONSTRAINT `project_monthly_additional_costs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_monthly_fees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `year` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `base_monthly_fee` decimal(12,2) NOT NULL,
  `rollover_from_previous` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_available_fee` decimal(12,2) NOT NULL,
  `hours_worked` decimal(8,2) NOT NULL DEFAULT 0.00,
  `hours_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `amount_invoiced_from_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `additional_costs_in_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `additional_costs_outside_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_invoiced` decimal(12,2) NOT NULL DEFAULT 0.00,
  `rollover_to_next` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_finalized` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_monthly_fees_project_id_year_month_unique` (`project_id`,`year`,`month`),
  KEY `project_monthly_fees_project_id_year_month_index` (`project_id`,`year`,`month`),
  KEY `project_monthly_fees_year_month_is_finalized_index` (`year`,`month`,`is_finalized`),
  CONSTRAINT `project_monthly_fees_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `service_id` bigint(20) unsigned NOT NULL,
  `custom_name` varchar(255) DEFAULT NULL COMMENT 'Custom name for this service instance (e.g. Webdesign example.com)',
  `import_status` enum('pending','imported','failed') NOT NULL DEFAULT 'pending' COMMENT 'Status of service structure import',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_services_service_id_foreign` (`service_id`),
  KEY `project_services_project_id_added_at_index` (`project_id`,`added_at`),
  CONSTRAINT `project_services_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_social_mentions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned DEFAULT NULL,
  `social_mention_id` bigint(20) unsigned NOT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `assignment_method` enum('automatic','manual','ai_suggested') NOT NULL DEFAULT 'manual',
  `confidence_score` int(11) NOT NULL DEFAULT 100,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_social_mentions_project_id_social_mention_id_unique` (`project_id`,`social_mention_id`),
  KEY `project_social_mentions_social_mention_id_foreign` (`social_mention_id`),
  KEY `project_social_mentions_campaign_id_created_at_index` (`campaign_id`,`created_at`),
  KEY `project_social_mentions_assigned_by_foreign` (`assigned_by`),
  CONSTRAINT `project_social_mentions_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_social_mentions_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `project_media_campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_social_mentions_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_social_mentions_social_mention_id_foreign` FOREIGN KEY (`social_mention_id`) REFERENCES `social_media_mentions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_milestone_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','in_progress','completed','on_hold') NOT NULL DEFAULT 'pending',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `fee_type` enum('in_fee','extended') NOT NULL DEFAULT 'in_fee',
  `pricing_type` enum('fixed_price','hourly_rate') NOT NULL DEFAULT 'hourly_rate',
  `fixed_price` decimal(10,2) DEFAULT NULL,
  `hourly_rate_override` decimal(8,2) DEFAULT NULL,
  `estimated_hours` decimal(8,2) DEFAULT NULL,
  `source_type` enum('manual','template','service') DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `is_service_item` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Indicates if this task was imported from a service',
  `service_name` varchar(255) DEFAULT NULL COMMENT 'Custom service name',
  `service_color` varchar(7) DEFAULT '#3B82F6' COMMENT 'Color for visual identification',
  `original_service_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Reference to original service',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_tasks_project_milestone_id_sort_order_index` (`project_milestone_id`,`sort_order`),
  KEY `project_tasks_project_milestone_id_status_index` (`project_milestone_id`,`status`),
  KEY `project_tasks_source_type_source_id_index` (`source_type`,`source_id`),
  CONSTRAINT `project_tasks_project_milestone_id_foreign` FOREIGN KEY (`project_milestone_id`) REFERENCES `project_milestones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `category` varchar(100) NOT NULL DEFAULT 'general',
  `estimated_total_hours` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estimated_total_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned NOT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `total_estimated_hours` decimal(8,2) DEFAULT NULL,
  `estimated_total_value` decimal(10,2) DEFAULT NULL,
  `estimated_duration_days` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_templates_category_is_active_index` (`category`,`is_active`),
  KEY `project_templates_created_by_index` (`created_by`),
  CONSTRAINT `project_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_override` enum('project_manager','user','reader') DEFAULT NULL,
  `can_edit_fee` tinyint(1) NOT NULL DEFAULT 0,
  `can_view_financials` tinyint(1) NOT NULL DEFAULT 0,
  `can_log_time` tinyint(1) NOT NULL DEFAULT 1,
  `can_approve_time` tinyint(1) NOT NULL DEFAULT 0,
  `added_by` bigint(20) unsigned NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_users_project_id_user_id_unique` (`project_id`,`user_id`),
  KEY `project_users_user_id_foreign` (`user_id`),
  KEY `project_users_project_id_role_override_index` (`project_id`,`role_override`),
  KEY `project_users_added_by_index` (`added_by`),
  CONSTRAINT `project_users_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`),
  CONSTRAINT `project_users_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `template_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('draft','active','completed','cancelled','on_hold') NOT NULL DEFAULT 'draft',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `monthly_fee` decimal(12,2) DEFAULT NULL,
  `total_value` decimal(12,2) DEFAULT NULL,
  `fee_start_date` date DEFAULT NULL,
  `fee_rollover_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `default_hourly_rate` decimal(8,2) DEFAULT NULL,
  `main_invoicing_company_id` bigint(20) unsigned DEFAULT NULL,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 21.00,
  `invoice_template_id` bigint(20) unsigned DEFAULT NULL,
  `billing_frequency` enum('monthly','quarterly','milestone','project_completion','custom') NOT NULL DEFAULT 'monthly',
  `billing_interval_days` int(11) DEFAULT NULL COMMENT 'Number of days for custom billing interval',
  `next_billing_date` date DEFAULT NULL COMMENT 'Next scheduled billing date',
  `last_billing_date` date DEFAULT NULL COMMENT 'Last billing date',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projects_customer_id_status_index` (`customer_id`,`status`),
  KEY `projects_main_invoicing_company_id_status_index` (`main_invoicing_company_id`,`status`),
  KEY `projects_template_id_index` (`template_id`),
  KEY `projects_company_id_foreign` (`company_id`),
  KEY `projects_created_by_foreign` (`created_by`),
  KEY `projects_updated_by_foreign` (`updated_by`),
  KEY `projects_deleted_by_foreign` (`deleted_by`),
  KEY `projects_invoice_template_id_foreign` (`invoice_template_id`),
  CONSTRAINT `projects_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `projects_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_invoice_template_id_foreign` FOREIGN KEY (`invoice_template_id`) REFERENCES `invoice_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_main_invoicing_company_id_foreign` FOREIGN KEY (`main_invoicing_company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `projects_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `project_templates` (`id`),
  CONSTRAINT `projects_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rss_feed_cache` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_id` bigint(20) unsigned NOT NULL,
  `guid` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `link` text NOT NULL,
  `description` text DEFAULT NULL,
  `pub_date` datetime NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `raw_content` text DEFAULT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT 0,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rss_feed_cache_guid_unique` (`guid`),
  KEY `rss_feed_cache_source_id_processed_index` (`source_id`,`processed`),
  KEY `rss_feed_cache_pub_date_index` (`pub_date`),
  CONSTRAINT `rss_feed_cache_source_id_foreign` FOREIGN KEY (`source_id`) REFERENCES `media_sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2198 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_activities_user_id_foreign` (`user_id`),
  KEY `service_activities_service_id_index` (`service_id`),
  KEY `service_activities_action_index` (`action`),
  KEY `service_activities_created_at_index` (`created_at`),
  CONSTRAINT `service_activities_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `service_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_categories_sort_order_is_active_index` (`sort_order`,`is_active`),
  KEY `service_categories_created_by_foreign` (`created_by`),
  KEY `service_categories_updated_by_foreign` (`updated_by`),
  KEY `idx_service_categories_company_status` (`company_id`,`status`),
  KEY `idx_service_categories_company_active` (`company_id`,`is_active`),
  KEY `idx_service_categories_sort_order` (`sort_order`),
  CONSTRAINT `service_categories_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `service_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `service_categories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_milestones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `estimated_hours` decimal(8,2) DEFAULT NULL,
  `included_in_price` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_milestones_service_id_sort_order_index` (`service_id`,`sort_order`),
  CONSTRAINT `service_milestones_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_milestone_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `estimated_hours` decimal(8,2) DEFAULT NULL,
  `included_in_price` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_tasks_service_milestone_id_sort_order_index` (`service_milestone_id`,`sort_order`),
  CONSTRAINT `service_tasks_service_milestone_id_foreign` FOREIGN KEY (`service_milestone_id`) REFERENCES `service_milestones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sku_code` varchar(255) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `estimated_hours` decimal(8,2) DEFAULT NULL,
  `is_package` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_service_category_id_is_active_index` (`service_category_id`,`is_active`),
  KEY `services_sku_code_index` (`sku_code`),
  KEY `services_created_by_foreign` (`created_by`),
  KEY `services_updated_by_foreign` (`updated_by`),
  CONSTRAINT `services_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_service_category_id_foreign` FOREIGN KEY (`service_category_id`) REFERENCES `service_categories` (`id`),
  CONSTRAINT `services_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'string',
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`),
  KEY `settings_group_key_index` (`group`,`key`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `simplified_theme_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `preset_name` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `primary_color` varchar(7) DEFAULT '#3b82f6',
  `secondary_color` varchar(7) DEFAULT '#10b981',
  `accent_color` varchar(7) DEFAULT '#f59e0b',
  `background_style` enum('solid','gradient','pattern') DEFAULT 'gradient',
  `background_color` varchar(7) DEFAULT '#f8fafc',
  `gradient_start` varchar(7) DEFAULT '#f8fafc',
  `gradient_end` varchar(7) DEFAULT '#e2e8f0',
  `gradient_direction` varchar(20) DEFAULT 'to bottom right',
  `font_family` varchar(100) DEFAULT 'Inter',
  `font_size_base` enum('10px','11px','12px','13px','14px','15px','16px') DEFAULT '14px',
  `border_radius` varchar(20) DEFAULT 'medium',
  `menu_style` enum('teamleader','classic','minimal') NOT NULL DEFAULT 'teamleader',
  `button_style` varchar(20) DEFAULT 'rounded',
  `button_size` enum('small','normal','medium','large') DEFAULT 'normal',
  `sidebar_style` enum('light','dark','colored') DEFAULT 'dark',
  `sidebar_background_color` varchar(7) NOT NULL DEFAULT '#1e293b',
  `sidebar_text_color` varchar(7) NOT NULL DEFAULT '#94a3b8',
  `sidebar_active_color` varchar(7) NOT NULL DEFAULT '#14b8a6',
  `top_nav_style` enum('tabs','pills','underline') NOT NULL DEFAULT 'tabs',
  `top_nav_active_color` varchar(7) NOT NULL DEFAULT '#14b8a6',
  `sidebar_icon_size` enum('small','medium','large') NOT NULL DEFAULT 'medium',
  `sidebar_text_size` enum('small','medium','large') NOT NULL DEFAULT 'small',
  `topbar_background_color` varchar(7) DEFAULT NULL,
  `view_header_title_size` enum('small','medium','large') NOT NULL DEFAULT 'medium',
  `view_header_padding` enum('compact','normal','spacious') NOT NULL DEFAULT 'normal',
  `view_header_auto_scale` tinyint(1) NOT NULL DEFAULT 0,
  `sidebar_width` varchar(10) DEFAULT '16rem',
  `header_style` enum('light','dark','colored','transparent') DEFAULT 'light',
  `card_style` enum('flat','elevated','bordered') DEFAULT 'elevated',
  `table_style` enum('simple','striped','bordered','hoverable') DEFAULT 'striped',
  `table_header_style` enum('light','dark','colored','bold') DEFAULT 'light',
  `animation_speed` enum('none','slow','normal','fast') DEFAULT 'normal',
  `enable_animations` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `header_font_size` varchar(20) DEFAULT 'normal',
  `line_height` varchar(20) DEFAULT 'normal',
  `header_spacing` enum('tight','normal','relaxed') NOT NULL DEFAULT 'normal',
  `header_title_size` enum('sm','base','lg','xl','2xl','3xl','4xl') NOT NULL DEFAULT 'xl',
  `header_title_weight` enum('normal','medium','semibold','bold','extrabold') NOT NULL DEFAULT 'bold',
  `button_radius` varchar(20) DEFAULT 'medium',
  `button_text_color` varchar(20) DEFAULT '#ffffff',
  `table_row_padding` varchar(20) DEFAULT 'normal',
  `danger_color` varchar(7) DEFAULT '#ef4444',
  `text_color` varchar(7) DEFAULT '#1f2937',
  `muted_text_color` varchar(7) DEFAULT '#6b7280',
  `card_padding` enum('none','small','normal','large') DEFAULT 'normal',
  `header_height` varchar(20) DEFAULT 'normal',
  `table_striped` tinyint(1) DEFAULT 0,
  `table_hover_effect` varchar(20) DEFAULT 'light',
  `header_padding` varchar(20) DEFAULT 'normal',
  `card_shadow` enum('none','small','medium','large') DEFAULT 'small',
  PRIMARY KEY (`id`),
  KEY `idx_company_active` (`company_id`,`is_active`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_engagement_metrics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `social_mention_id` bigint(20) unsigned NOT NULL,
  `likes_count` int(11) NOT NULL DEFAULT 0,
  `shares_count` int(11) NOT NULL DEFAULT 0,
  `comments_count` int(11) NOT NULL DEFAULT 0,
  `views_count` int(11) DEFAULT NULL,
  `engagement_rate` decimal(5,2) DEFAULT NULL,
  `measured_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_engagement_metrics_social_mention_id_measured_at_index` (`social_mention_id`,`measured_at`),
  CONSTRAINT `social_engagement_metrics_social_mention_id_foreign` FOREIGN KEY (`social_mention_id`) REFERENCES `social_media_mentions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_media_mentions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_id` bigint(20) unsigned NOT NULL,
  `platform_post_id` varchar(255) NOT NULL,
  `author_name` varchar(255) NOT NULL,
  `author_handle` varchar(255) DEFAULT NULL,
  `author_profile_url` varchar(255) DEFAULT NULL,
  `author_followers` int(11) DEFAULT NULL,
  `author_verified` tinyint(1) NOT NULL DEFAULT 0,
  `content` text NOT NULL,
  `hashtags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`hashtags`)),
  `mentions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`mentions`)),
  `urls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`urls`)),
  `media_urls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`media_urls`)),
  `post_url` varchar(255) DEFAULT NULL,
  `published_at` datetime NOT NULL,
  `likes_count` int(11) NOT NULL DEFAULT 0,
  `shares_count` int(11) NOT NULL DEFAULT 0,
  `comments_count` int(11) NOT NULL DEFAULT 0,
  `views_count` int(11) DEFAULT NULL,
  `engagement_rate` decimal(10,2) DEFAULT NULL,
  `post_type` enum('post','reply','share','story') NOT NULL DEFAULT 'post',
  `in_reply_to` varchar(255) DEFAULT NULL,
  `is_repost` tinyint(1) NOT NULL DEFAULT 0,
  `raw_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `social_media_mentions_platform_post_id_unique` (`platform_post_id`),
  KEY `social_media_mentions_source_id_published_at_index` (`source_id`,`published_at`),
  KEY `social_media_mentions_author_handle_index` (`author_handle`),
  KEY `social_media_mentions_engagement_rate_index` (`engagement_rate`),
  CONSTRAINT `social_media_mentions_source_id_foreign` FOREIGN KEY (`source_id`) REFERENCES `social_media_sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_media_sources` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `platform` enum('twitter','linkedin','facebook','instagram','youtube') NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `account_id` varchar(255) DEFAULT NULL,
  `api_credentials` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`api_credentials`)),
  `monitoring_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`monitoring_config`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `check_frequency` int(11) NOT NULL DEFAULT 5,
  `last_checked_at` timestamp NULL DEFAULT NULL,
  `last_collected_at` timestamp NULL DEFAULT NULL,
  `last_post_id` varchar(255) DEFAULT NULL,
  `rate_limit_remaining` int(11) DEFAULT NULL,
  `rate_limit_reset_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_media_sources_platform_is_active_index` (`platform`,`is_active`),
  KEY `social_media_sources_last_checked_at_index` (`last_checked_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `template_milestones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_template_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `days_from_start` int(11) DEFAULT NULL,
  `duration_days` int(11) DEFAULT NULL,
  `fee_type` enum('in_fee','extended') NOT NULL DEFAULT 'in_fee',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `pricing_type` enum('fixed_price','hourly_rate') NOT NULL DEFAULT 'hourly_rate',
  `default_fixed_price` decimal(10,2) DEFAULT NULL,
  `default_hourly_rate` decimal(8,2) DEFAULT NULL,
  `estimated_hours` decimal(8,2) DEFAULT NULL,
  `deliverables` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `template_milestones_project_template_id_sort_order_index` (`project_template_id`,`sort_order`),
  CONSTRAINT `template_milestones_project_template_id_foreign` FOREIGN KEY (`project_template_id`) REFERENCES `project_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `template_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `template_milestone_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `duration_days` int(11) DEFAULT NULL,
  `fee_type` enum('in_fee','extended') NOT NULL DEFAULT 'in_fee',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `pricing_type` enum('fixed_price','hourly_rate') NOT NULL DEFAULT 'hourly_rate',
  `default_fixed_price` decimal(10,2) DEFAULT NULL,
  `default_hourly_rate` decimal(8,2) DEFAULT NULL,
  `estimated_hours` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `template_tasks_template_milestone_id_sort_order_index` (`template_milestone_id`,`sort_order`),
  CONSTRAINT `template_tasks_template_milestone_id_foreign` FOREIGN KEY (`template_milestone_id`) REFERENCES `template_milestones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `time_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `project_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `project_milestone_id` bigint(20) unsigned DEFAULT NULL,
  `project_task_id` bigint(20) unsigned DEFAULT NULL,
  `project_subtask_id` bigint(20) unsigned DEFAULT NULL,
  `entry_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `hours` decimal(8,2) NOT NULL,
  `minutes` int(11) NOT NULL COMMENT 'Aantal minuten gewerkt (in stappen van 5)',
  `description` text NOT NULL,
  `ai_feedback` enum('good','bad','adjusted') DEFAULT NULL,
  `ai_improved_description` text DEFAULT NULL,
  `ai_suggestion_used` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `hourly_rate_used` decimal(8,2) NOT NULL,
  `status` enum('draft','pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `is_billable` enum('pending','billable','non_billable') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_line_id` bigint(20) unsigned DEFAULT NULL,
  `is_invoiced` tinyint(1) NOT NULL DEFAULT 0,
  `is_finalized` tinyint(1) NOT NULL DEFAULT 0,
  `finalized_at` timestamp NULL DEFAULT NULL,
  `final_invoice_number` varchar(255) DEFAULT NULL,
  `is_service_item` tinyint(1) NOT NULL DEFAULT 0,
  `original_service_id` bigint(20) unsigned DEFAULT NULL,
  `invoiced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `invoiced_hours` decimal(8,2) DEFAULT NULL COMMENT 'Hours as invoiced (may differ from actual hours)',
  `invoiced_rate` decimal(8,2) DEFAULT NULL COMMENT 'Hourly rate as invoiced (may differ from hourly_rate_used)',
  `invoiced_description` text DEFAULT NULL COMMENT 'Description as shown on invoice (may differ from description)',
  `invoiced_modified_at` datetime DEFAULT NULL COMMENT 'When invoiced data was last modified',
  `invoiced_modified_by` bigint(20) unsigned DEFAULT NULL COMMENT 'User who last modified invoiced data',
  `was_deferred` tinyint(1) NOT NULL DEFAULT 0,
  `deferred_at` datetime DEFAULT NULL,
  `deferred_by` bigint(20) unsigned DEFAULT NULL,
  `defer_reason` text DEFAULT NULL,
  `was_previously_deferred` tinyint(1) NOT NULL DEFAULT 0,
  `previous_deferred_at` timestamp NULL DEFAULT NULL,
  `previous_deferred_by` bigint(20) unsigned DEFAULT NULL,
  `previous_defer_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `time_entries_customer_id_foreign` (`customer_id`),
  KEY `time_entries_project_milestone_id_foreign` (`project_milestone_id`),
  KEY `time_entries_project_task_id_foreign` (`project_task_id`),
  KEY `time_entries_project_subtask_id_foreign` (`project_subtask_id`),
  KEY `time_entries_approved_by_foreign` (`approved_by`),
  KEY `time_entries_user_id_date_index` (`user_id`,`entry_date`),
  KEY `time_entries_project_id_status_index` (`project_id`,`status`),
  KEY `time_entries_date_status_is_billable_index` (`entry_date`,`status`,`is_billable`),
  KEY `time_entries_company_id_date_index` (`company_id`,`entry_date`),
  KEY `time_entries_invoice_line_id_foreign` (`invoice_line_id`),
  KEY `time_entries_invoice_id_is_invoiced_index` (`invoice_id`,`is_invoiced`),
  KEY `time_entries_created_by_foreign` (`created_by`),
  KEY `time_entries_updated_by_foreign` (`updated_by`),
  KEY `time_entries_invoiced_modified_by_foreign` (`invoiced_modified_by`),
  KEY `time_entries_deferred_by_foreign` (`deferred_by`),
  KEY `time_entries_previous_deferred_by_foreign` (`previous_deferred_by`),
  CONSTRAINT `time_entries_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `time_entries_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `time_entries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `time_entries_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `time_entries_deferred_by_foreign` FOREIGN KEY (`deferred_by`) REFERENCES `users` (`id`),
  CONSTRAINT `time_entries_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  CONSTRAINT `time_entries_invoice_line_id_foreign` FOREIGN KEY (`invoice_line_id`) REFERENCES `invoice_lines` (`id`),
  CONSTRAINT `time_entries_invoiced_modified_by_foreign` FOREIGN KEY (`invoiced_modified_by`) REFERENCES `users` (`id`),
  CONSTRAINT `time_entries_previous_deferred_by_foreign` FOREIGN KEY (`previous_deferred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `time_entries_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  CONSTRAINT `time_entries_project_milestone_id_foreign` FOREIGN KEY (`project_milestone_id`) REFERENCES `project_milestones` (`id`),
  CONSTRAINT `time_entries_project_subtask_id_foreign` FOREIGN KEY (`project_subtask_id`) REFERENCES `project_subtasks` (`id`),
  CONSTRAINT `time_entries_project_task_id_foreign` FOREIGN KEY (`project_task_id`) REFERENCES `project_tasks` (`id`),
  CONSTRAINT `time_entries_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `time_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_media_mentions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `monitor_id` bigint(20) unsigned NOT NULL,
  `source_name` varchar(255) NOT NULL,
  `article_title` text NOT NULL,
  `article_url` text NOT NULL,
  `article_excerpt` text DEFAULT NULL,
  `published_at` datetime NOT NULL,
  `relevance_score` int(11) NOT NULL DEFAULT 0,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_starred` tinyint(1) NOT NULL DEFAULT 0,
  `ai_summary` text DEFAULT NULL,
  `found_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`found_keywords`)),
  `sentiment` enum('positive','neutral','negative') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_media_mentions_user_id_is_read_index` (`user_id`,`is_read`),
  KEY `user_media_mentions_monitor_id_published_at_index` (`monitor_id`,`published_at`),
  KEY `user_media_mentions_relevance_score_index` (`relevance_score`),
  CONSTRAINT `user_media_mentions_monitor_id_foreign` FOREIGN KEY (`monitor_id`) REFERENCES `user_media_monitors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_media_mentions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_media_monitors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`keywords`)),
  `exclude_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`exclude_keywords`)),
  `social_platforms` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_platforms`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `monitor_social` tinyint(1) NOT NULL DEFAULT 0,
  `email_alerts` tinyint(1) NOT NULL DEFAULT 0,
  `alert_frequency` enum('realtime','hourly','daily') NOT NULL DEFAULT 'daily',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_media_monitors_user_id_is_active_index` (`user_id`,`is_active`),
  KEY `user_media_monitors_campaign_id_index` (`campaign_id`),
  CONSTRAINT `user_media_monitors_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `project_media_campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_media_monitors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_ms_graph_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `account_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`account_info`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_ms_graph_tokens_user_id_index` (`user_id`),
  KEY `user_ms_graph_tokens_email_index` (`email`),
  KEY `user_ms_graph_tokens_expires_at_index` (`expires_at`),
  CONSTRAINT `user_ms_graph_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_social_mentions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `monitor_id` bigint(20) unsigned NOT NULL,
  `social_mention_id` bigint(20) unsigned NOT NULL,
  `relevance_score` int(11) NOT NULL DEFAULT 0,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_starred` tinyint(1) NOT NULL DEFAULT 0,
  `requires_response` tinyint(1) NOT NULL DEFAULT 0,
  `response_draft` text DEFAULT NULL,
  `responded_at` datetime DEFAULT NULL,
  `sentiment` enum('positive','neutral','negative') DEFAULT NULL,
  `matched_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`matched_keywords`)),
  `ai_summary` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_monitor_social_unique` (`user_id`,`monitor_id`,`social_mention_id`),
  KEY `user_social_mentions_monitor_id_foreign` (`monitor_id`),
  KEY `user_social_mentions_social_mention_id_foreign` (`social_mention_id`),
  KEY `user_social_mentions_user_id_is_read_index` (`user_id`,`is_read`),
  KEY `user_social_mentions_relevance_score_index` (`relevance_score`),
  CONSTRAINT `user_social_mentions_monitor_id_foreign` FOREIGN KEY (`monitor_id`) REFERENCES `user_media_monitors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_social_mentions_social_mention_id_foreign` FOREIGN KEY (`social_mention_id`) REFERENCES `social_media_mentions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_social_mentions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `role` enum('super_admin','admin','project_manager','user','reader') NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `auto_approve_time_entries` tinyint(1) NOT NULL DEFAULT 0,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `idx_company_id` (`company_id`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

