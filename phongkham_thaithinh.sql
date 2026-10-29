-- ============================================================================
-- CƠ SỞ DỮ LIỆU: phongkham_thaithinh (Phòng khám Đa khoa 125 Thái Thịnh)
-- Ngày xuất: 2026-10-26 16:23:25
-- Chuẩn mã hóa: UTF8MB4 (MySQL / MariaDB)
-- ============================================================================

SET NAMES utf8mb4;
SET TIME_ZONE = '+07:00';
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE IF NOT EXISTS `phongkham_thaithinh` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `phongkham_thaithinh`;

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `appointment_status_histories`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `appointment_status_histories`;
CREATE TABLE `appointment_status_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `appointment_id` bigint(20) unsigned NOT NULL,
  `from_status` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `action_role` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'admin, doctor, patient, system',
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appointment_status_histories_changed_by_user_id_foreign` (`changed_by_user_id`),
  KEY `appt_hist_idx` (`appointment_id`,`created_at`),
  CONSTRAINT `appointment_status_histories_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointment_status_histories_changed_by_user_id_foreign` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `appointment_status_histories` (4 bản ghi)
INSERT INTO `appointment_status_histories` (`id`, `appointment_id`, `from_status`, `to_status`, `changed_by_user_id`, `action_role`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'pending', 6, 'patient', 'Bệnh nhân đặt lịch trực tuyến qua hệ thống', '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(2, 1, 'pending', 'confirmed', 1, 'admin', 'Admin xác nhận lịch hẹn hợp lệ và đã thanh toán', '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(3, 2, 'confirmed', 'completed', 3, 'doctor', 'Bác sĩ hoàn thành khám bệnh và kê đơn thuốc', '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(4, 3, NULL, 'pending', 8, 'patient', 'Bệnh nhân tạo lịch hẹn mới', '2026-10-25 17:51:40', '2026-10-25 17:51:40');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `appointments`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `appointment_code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idempotency_key` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `booking_for` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'self' COMMENT 'self, relative',
  `relative_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `relative_dob` date DEFAULT NULL,
  `relative_gender` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `relative_relationship` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Con nhỏ, Bố/Mẹ, Vợ/Chồng, Khác',
  `doctor_id` bigint(20) unsigned NOT NULL,
  `service_id` bigint(20) unsigned NOT NULL,
  `time_slot_id` bigint(20) unsigned DEFAULT NULL,
  `appointment_date` datetime NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` enum('success','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'failed',
  `total` decimal(12,2) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_ref` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refund_status` enum('none','pending','refunded','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `refund_amount` decimal(12,2) DEFAULT NULL,
  `refund_note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `medical_examination` enum('Ca sáng (07:30 - 11:30)','Ca chiều (13:00 - 17:00)') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `appointments_appointment_code_unique` (`appointment_code`),
  KEY `appointments_patient_id_foreign` (`patient_id`),
  KEY `appointments_doctor_id_foreign` (`doctor_id`),
  KEY `appointments_service_id_foreign` (`service_id`),
  KEY `appointments_time_slot_id_foreign` (`time_slot_id`),
  KEY `appointments_cancelled_by_foreign` (`cancelled_by`),
  KEY `appointments_idempotency_key_index` (`idempotency_key`),
  CONSTRAINT `appointments_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `appointments_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`),
  CONSTRAINT `appointments_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `appointments_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `appointments_time_slot_id_foreign` FOREIGN KEY (`time_slot_id`) REFERENCES `time_slots` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `appointments` (4 bản ghi)
INSERT INTO `appointments` (`id`, `appointment_code`, `idempotency_key`, `patient_id`, `booking_for`, `relative_name`, `relative_dob`, `relative_gender`, `relative_relationship`, `doctor_id`, `service_id`, `time_slot_id`, `appointment_date`, `status`, `payment_status`, `total`, `total_amount`, `payment_method`, `transaction_id`, `transaction_ref`, `payment_reference`, `refund_status`, `refund_amount`, `refund_note`, `paid_at`, `checked_in_at`, `medical_examination`, `note`, `cancellation_reason`, `cancelled_at`, `created_at`, `updated_at`, `cancelled_by`) VALUES
(1, 'TT-20261025-06C1', NULL, 1, 'self', NULL, NULL, NULL, NULL, 1, 1, 2, '2026-10-25 08:00:00', 'confirmed', 'success', NULL, NULL, NULL, NULL, NULL, NULL, 'none', NULL, NULL, '2026-10-25 17:51:40', NULL, 'Ca sáng (07:30 - 11:30)', 'Bệnh nhân cảm thấy hồi hộp, đau tức nhẹ ngực trái khi gắng sức.', NULL, NULL, '2026-10-25 17:51:40', '2026-10-25 17:51:40', NULL),
(2, 'TT-20261025-DF19', NULL, 2, 'self', NULL, NULL, NULL, NULL, 2, 3, NULL, '2026-10-24 08:30:00', 'completed', 'success', NULL, NULL, NULL, NULL, NULL, NULL, 'none', NULL, NULL, '2026-10-23 22:00:00', NULL, 'Ca sáng (07:30 - 11:30)', 'Bệnh nhân nghẹt mũi, khàn giọng 3 ngày.', NULL, NULL, '2026-10-25 17:51:40', '2026-10-25 17:51:40', NULL),
(3, 'TT-20261025-D9A3', NULL, 3, 'self', NULL, NULL, NULL, NULL, 1, 4, 16, '2026-10-26 07:30:00', 'pending', 'failed', NULL, NULL, NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, NULL, 'Ca sáng (07:30 - 11:30)', 'Đưa bé đi kiểm tra dinh dưỡng và khám đường hô hấp định kỳ.', NULL, NULL, '2026-10-25 17:51:40', '2026-10-25 17:51:40', NULL),
(4, 'TT-20261025-A62E', NULL, 4, 'self', NULL, NULL, NULL, NULL, 4, 5, NULL, '2026-10-26 14:00:00', 'cancelled', 'failed', NULL, NULL, NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, NULL, 'Ca chiều (13:00 - 17:00)', 'Đã hủy lịch hẹn.', 'Bận lịch công tác đột xuất vào ngày mai, xin dời sang tuần sau.', '2026-10-25 17:51:40', '2026-10-25 17:51:40', '2026-10-25 17:51:40', 9);

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `departments`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `fulltext_description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `departments` (5 bản ghi)
INSERT INTO `departments` (`id`, `name`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Khoa Tim mạch', 'Chuyên chẩn đoán và điều trị các bệnh lý tim mạch, mạch vành, tăng huyết áp bằng kỹ thuật cao.', NULL, 1, '2026-10-25 17:51:33', '2026-10-25 17:51:33'),
(2, 'Khoa Tai Mũi Họng', 'Khám và điều trị các bệnh lý tai mũi họng với hệ thống nội soi ống mềm hiện đại không đau.', NULL, 1, '2026-10-25 17:51:33', '2026-10-25 17:51:33'),
(3, 'Khoa Nhi', 'Chăm sóc sức khỏe toàn diện cho trẻ em, theo dõi phát triển thể chất và tiêm chủng phòng bệnh.', NULL, 1, '2026-10-25 17:51:33', '2026-10-25 17:51:33'),
(4, 'Khoa Nội tổng quát', 'Tầm soát, phát hiện sớm và điều trị các bệnh lý mạn tính: đái tháo đường, tiêu hóa, gan mật.', NULL, 1, '2026-10-25 17:51:33', '2026-10-25 17:51:33'),
(5, 'Khoa Sản phụ khoa', 'Khám thai định kỳ, siêu âm 4D sàng lọc dị tật và điều trị chuyên sâu bệnh lý phụ khoa.', NULL, 1, '2026-10-25 17:51:33', '2026-10-25 17:51:33');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `disease_symptoms`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `disease_symptoms`;
CREATE TABLE `disease_symptoms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `disease_id` bigint(20) unsigned NOT NULL,
  `symptom_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `disease_symptoms_disease_id_foreign` (`disease_id`),
  CONSTRAINT `disease_symptoms_disease_id_foreign` FOREIGN KEY (`disease_id`) REFERENCES `diseases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `disease_symptoms` (15 bản ghi)
INSERT INTO `disease_symptoms` (`id`, `disease_id`, `symptom_name`, `created_at`, `updated_at`) VALUES
(1, 1, 'Đau tức ngực trái', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(2, 1, 'Khó thở khi gắng sức', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(3, 1, 'Hồi hộp đánh trống ngực', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(4, 2, 'Đau đầu vùng chẩm', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(5, 2, 'Hoa mắt chóng mặt', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(6, 2, 'Huyết áp tăng cao trên 140/90 mmHg', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(7, 3, 'Ho khan kéo dài', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(8, 3, 'Đau rát họng', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(9, 3, 'Khàn tiếng, sốt nhẹ về chiều', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(10, 4, 'Đau thượng vị âm ỉ', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(11, 4, 'Ợ hơi, ợ chua nóng rát', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(12, 4, 'Đầy bụng khó tiêu sau ăn', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(13, 5, 'Sốt cao trên 38.5 độ', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(14, 5, 'Ho có đờm', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(15, 5, 'Trẻ quấy khóc, biếng ăn', '2026-10-26 16:23:01', '2026-10-26 16:23:01');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `diseases`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `diseases`;
CREATE TABLE `diseases` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` bigint(20) unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `diseases_department_id_foreign` (`department_id`),
  CONSTRAINT `diseases_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `diseases` (5 bản ghi)
INSERT INTO `diseases` (`id`, `name`, `department_id`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Cảm cúm thông thường (Flu)', 2, 'Nhiễm virus đường hô hấp trên gây sốt, ho, sổ mũi', NULL, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(2, 'Viêm họng cấp (Pharyngitis)', 2, 'Viêm niêm mạc họng do vi khuẩn hoặc virus', NULL, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(3, 'Viêm loét dạ dày - tá tràng', 4, 'Đau thượng vị, ợ hơi, ợ chua liên quan đến acid dạ dày', NULL, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(4, 'Tăng huyết áp vô căn', 1, 'Huyết áp tâm thu >= 140 mmHg hoặc tâm trương >= 90 mmHg', NULL, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(5, 'Đái tháo đường Type 2', 4, 'Rối loạn chuyển hóa đường huyết mãn tính', NULL, '2026-10-25 17:51:40', '2026-10-25 17:51:40');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `doctors`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `doctors`;
CREATE TABLE `doctors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `specialization` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `license_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `doctors_user_id_foreign` (`user_id`),
  KEY `doctors_department_id_foreign` (`department_id`),
  CONSTRAINT `doctors_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `doctors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `doctors` (4 bản ghi)
INSERT INTO `doctors` (`id`, `user_id`, `department_id`, `specialization`, `is_active`, `license_number`, `license_image`, `avatar`, `description`, `birth_date`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Bác sĩ Chuyên khoa II Tim mạch', 1, '012456/HN-CCHN', NULL, NULL, 'Hơn 20 năm kinh nghiệm điều trị bệnh mạch vành, rối loạn nhịp tim tại các bệnh viện tuyến trung ương.', '1981-10-25', '2026-10-25 17:51:34', '2026-10-25 17:51:34'),
(2, 3, 2, 'Thạc sĩ Tai Mũi Họng', 1, '013789/HN-CCHN', NULL, NULL, 'Chuyên gia nội soi chẩn đoán sớm các bệnh lý vùng họng thanh quản và viêm xoang mũi.', '1981-10-25', '2026-10-25 17:51:34', '2026-10-25 17:51:34'),
(3, 4, 3, 'Bác sĩ Chuyên khoa I Nhi khoa', 1, '015678/HN-CCHN', NULL, NULL, '15 năm kinh nghiệm khám chữa bệnh hô hấp nhi, dị ứng và tư vấn tiêm chủng an toàn.', '1981-10-25', '2026-10-25 17:51:35', '2026-10-25 17:51:35'),
(4, 5, 4, 'Bác sĩ Chuyên khoa II Nội khoa', 1, '019234/HN-CCHN', NULL, NULL, 'Nguyên Trưởng khoa Khám bệnh, chuyên gia điều trị đái tháo đường và rối loạn chuyển hóa.', '1981-10-25', '2026-10-25 17:51:35', '2026-10-25 17:51:35');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `lab_tests`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `lab_tests`;
CREATE TABLE `lab_tests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `medical_record_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned NOT NULL,
  `test_type_id` bigint(20) unsigned DEFAULT NULL,
  `test_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_by` bigint(20) unsigned NOT NULL,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requested',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lab_tests_medical_record_id_foreign` (`medical_record_id`),
  KEY `lab_tests_department_id_foreign` (`department_id`),
  KEY `lab_tests_requested_by_foreign` (`requested_by`),
  KEY `lab_tests_uploaded_by_foreign` (`uploaded_by`),
  KEY `lab_tests_test_type_id_foreign` (`test_type_id`),
  CONSTRAINT `lab_tests_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lab_tests_medical_record_id_foreign` FOREIGN KEY (`medical_record_id`) REFERENCES `medical_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lab_tests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lab_tests_test_type_id_foreign` FOREIGN KEY (`test_type_id`) REFERENCES `test_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lab_tests_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `lab_tests` (2 bản ghi)
INSERT INTO `lab_tests` (`id`, `medical_record_id`, `department_id`, `test_type_id`, `test_name`, `image`, `images`, `note`, `requested_by`, `uploaded_by`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'Xét nghiệm Tổng phân tích tế bào máu ngoại vi', NULL, NULL, 'Bệnh nhân sốt cao nghi nhiễm khuẩn', 2, 1, 'completed', '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(2, 1, 1, NULL, 'Xét nghiệm Đường huyết đói (Glucose)', NULL, NULL, 'Kiểm tra đường huyết định kỳ', 2, NULL, 'requested', '2026-10-25 17:51:40', '2026-10-25 17:51:40');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `medical_records`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `medical_records`;
CREATE TABLE `medical_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `appointment_id` bigint(20) unsigned NOT NULL,
  `patient_id` bigint(20) unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diagnosis` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doctor_conclusion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prescription` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  PRIMARY KEY (`id`),
  KEY `medical_records_appointment_id_foreign` (`appointment_id`),
  KEY `medical_records_patient_id_foreign` (`patient_id`),
  CONSTRAINT `medical_records_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medical_records_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `medical_records` (2 bản ghi)
INSERT INTO `medical_records` (`id`, `appointment_id`, `patient_id`, `description`, `diagnosis`, `doctor_conclusion`, `prescription`, `notes`, `created_at`, `updated_at`, `image`, `images`) VALUES
(1, 2, 2, 'Nội soi vòm họng niêm mạc xung huyết, có dịch mủ rải rác khe giữa.', 'Viêm mũi xoang cấp, viêm thanh quản thể xuất tiết', 'Bệnh nhân đáp ứng tốt, cần kiêng uống nước lạnh và xịt rửa mũi nước muối sinh lý.', '[{\"name\":\"Augmentin 1g\",\"dosage\":\"2 vi\\u00ean\\/ng\\u00e0y chia 2 l\\u1ea7n\",\"duration\":\"5 ng\\u00e0y\"},{\"name\":\"Otrivin 0.1%\",\"dosage\":\"X\\u1ecbt m\\u0169i 2 l\\u1ea7n\\/ng\\u00e0y\",\"duration\":\"3 ng\\u00e0y\"}]', NULL, '2026-10-25 17:51:40', '2026-10-25 17:51:40', NULL, NULL),
(2, 1, 1, NULL, 'Tăng huyết áp độ 1 / Theo dõi thiếu máu cơ tim cục bộ (I10)', NULL, NULL, NULL, '2026-10-26 21:40:16', '2026-10-26 21:40:16', NULL, NULL);

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `medicines`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `medicines`;
CREATE TABLE `medicines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `generic_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dosage_form` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `strength` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 10,
  `indications` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contraindications` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `side_effects` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interactions` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dosage` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usage` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manufacturer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_prescription` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medicines_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `medicines` (5 bản ghi)
INSERT INTO `medicines` (`id`, `name`, `generic_name`, `brand_name`, `slug`, `category`, `dosage_form`, `strength`, `unit`, `price`, `stock`, `min_stock`, `indications`, `contraindications`, `side_effects`, `interactions`, `dosage`, `usage`, `note`, `manufacturer`, `origin`, `barcode`, `is_prescription`, `created_at`, `updated_at`) VALUES
(1, 'Paracetamol 500mg', 'Paracetamol', 'Panadol', 'paracetamol-500mg-354', 'Giảm đau, hạ sốt', 'Viên nén', '500mg', 'Viên', '2000.00', 500, 10, NULL, NULL, NULL, NULL, NULL, 'Uống 1-2 viên khi sốt trên 38.5 độ C', NULL, NULL, NULL, NULL, 0, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(2, 'Amoxicillin 500mg', 'Amoxicillin', 'Clamoxyl', 'amoxicillin-500mg-319', 'Kháng sinh', 'Viên nang', '500mg', 'Viên', '5000.00', 300, 10, NULL, NULL, NULL, NULL, NULL, 'Uống 1 viên x 3 lần/ngày sau ăn', NULL, NULL, NULL, NULL, 0, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(3, 'Berberin 50mg', 'Berberin clorid', 'Berberin', 'berberin-50mg-105', 'Tiêu hóa', 'Viên nén', '50mg', 'Viên', '1500.00', 450, 10, NULL, NULL, NULL, NULL, NULL, 'Uống 4-6 viên/lần, ngày 2 lần khi tiêu chảy', NULL, NULL, NULL, NULL, 0, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(4, 'Omeprazol 20mg', 'Omeprazole', 'Losec', 'omeprazol-20mg-947', 'Tiêu hóa, dạ dày', 'Viên nang tan trong ruột', '20mg', 'Viên', '8000.00', 200, 10, NULL, NULL, NULL, NULL, NULL, 'Uống 1 viên trước bữa ăn sáng 30 phút', NULL, NULL, NULL, NULL, 0, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(5, 'Panadol Extra Đỏ', 'Paracetamol + Caffeine', 'Panadol Extra', 'panadol-extra-do-710', 'Giảm đau nhanh', 'Viên nén', '500mg/65mg', 'Viên', '3000.00', 350, 10, NULL, NULL, NULL, NULL, NULL, 'Uống 1 viên khi đau đầu dữ dội', NULL, NULL, NULL, NULL, 0, '2026-10-25 17:51:40', '2026-10-25 17:51:40');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `messages`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint(20) unsigned NOT NULL,
  `receiver_id` bigint(20) unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_sender_id_foreign` (`sender_id`),
  KEY `messages_receiver_id_foreign` (`receiver_id`),
  CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `messages` (3 bản ghi)
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 6, 1, 'Chào phòng khám, tôi bị đau tức ngực nhẹ và hồi hộp thì nên đăng ký khám chuyên khoa nào ạ?', 1, '2026-10-26 16:23:25', '2026-10-26 16:23:25'),
(2, 1, 6, 'Chào bạn, với triệu chứng đau tức ngực và hồi hộp, bạn nên đăng ký khám tại Khoa Tim mạch (Tầng 2) cùng BS. CKII Nguyễn Văn Hùng nhé!', 1, '2026-10-26 16:23:25', '2026-10-26 16:23:25'),
(3, 6, 1, 'Cảm ơn phòng khám, tôi đã đặt lịch khám lúc 08:30 sáng mai và nhận được mã QR rồi ạ.', 0, '2026-10-26 16:23:25', '2026-10-26 16:23:25');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `migrations`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `migrations` (41 bản ghi)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(3, '2024_01_10_000000_add_profile_fields_to_users_table', 1),
(4, '2025_09_14_141823_create_roles_table', 1),
(5, '2025_09_14_142054_add_role_id_to_users_table', 1),
(6, '2025_09_14_142209_create_patients_table', 1),
(7, '2025_09_14_142342_create_departments_table', 1),
(8, '2025_09_14_142417_create_doctors_table', 1),
(9, '2025_09_14_142521_create_services_table', 1),
(10, '2025_09_14_142650_create_appointments_table', 1),
(11, '2025_09_14_142809_create_medical_records_table', 1),
(12, '2025_09_15_060115_add_name_email_to_patients_table', 1),
(13, '2025_09_23_051831_add_note_to_appointments_table', 1),
(14, '2025_10_13_222117_add_medical_examination_to_appointments_table', 1),
(15, '2025_10_17_211352_add_payment_status_to_appointments_table', 1),
(16, '2025_10_17_211353_add_paid_at_to_appointments_table', 1),
(17, '2025_10_18_160500_create_medical_records_table', 1),
(18, '2025_10_19_130409_add_avatar_to_doctors_table', 1),
(19, '2025_10_19_132823_add_avatar_to_patients_table', 1),
(20, '2025_10_19_143403_create_messages_table', 1),
(21, '2025_10_19_152339_add_birth_date_to_doctors_table', 1),
(22, '2025_10_22_162643_add_license_image_to_doctors_table', 1),
(23, '2025_11_07_135029_create_lab_tests_table', 1),
(24, '2025_11_07_143514_create_test_types_table', 1),
(25, '2025_11_07_143641_add_test_type_id_to_lab_tests_table', 1),
(26, '2025_11_21_195420_add_image_to_departments_table', 1),
(27, '2025_11_21_195632_add_image_to_services_table', 1),
(28, '2025_11_22_102856_create_password_reset_tokens_table', 1),
(29, '2025_12_12_165456_create_prescription_items_table', 1),
(30, '2025_12_12_170005_create_medicines_table', 1),
(31, '2025_12_18_000001_add_is_active_to_departments_table', 1),
(32, '2025_12_20_000000_add_fulltext_index_to_departments_description', 1),
(33, '2025_12_20_000001_create_service_symptoms_table', 1),
(34, '2025_12_20_000100_create_diseases_table', 1),
(35, '2025_12_20_000101_create_disease_symptoms_table', 1),
(36, '2026_01_01_000000_create_working_schedules_and_time_slots_tables', 1),
(37, '2026_01_01_000001_enhance_appointments_and_create_audit_histories', 1),
(38, '2026_01_01_000002_add_is_active_to_doctors_table', 1),
(39, '2026_01_01_000003_add_family_booking_and_checkin_fields_to_appointments', 1),
(40, '2026_10_23_230000_fix_schema_columns_and_constraints', 1),
(41, '2026_10_23_235500_sync_schema_and_backfill_data', 1);

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `password_reset_tokens`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_reset_tokens_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `patients`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `patients`;
CREATE TABLE `patients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insurance_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patients_user_id_unique` (`user_id`),
  CONSTRAINT `patients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `patients` (6 bản ghi)
INSERT INTO `patients` (`id`, `user_id`, `name`, `email`, `birthdate`, `gender`, `address`, `phone`, `insurance_number`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 6, 'Bùi Tuấn Anh', 'patient1@thaithinh.vn', '1995-04-12', 'Nam', 'Số 15 phố Thái Thịnh, Đống Đa, Hà Nội', '0987654321', NULL, NULL, '2026-10-25 17:51:36', '2026-10-25 17:51:36'),
(2, 7, 'Vũ Thị Hồng Nhung', 'patient2@thaithinh.vn', '1998-08-20', 'Nữ', 'Số 88 phố Giảng Võ, Ba Đình, Hà Nội', '0912345678', NULL, NULL, '2026-10-25 17:51:36', '2026-10-25 17:51:36'),
(3, 8, 'Nguyễn Minh Quang', 'patient3@thaithinh.vn', '1988-11-05', 'Nam', 'Số 42 Cầu Giấy, Cầu Giấy, Hà Nội', '0934567890', NULL, NULL, '2026-10-25 17:51:37', '2026-10-25 17:51:37'),
(4, 9, 'Lê Thu Trang', 'patient4@thaithinh.vn', '2000-01-15', 'Nữ', 'Số 209 Nguyễn Trãi, Thanh Xuân, Hà Nội', '0976543210', NULL, NULL, '2026-10-25 17:51:37', '2026-10-25 17:51:37'),
(5, 10, 'Hoàng Đức Trọng', 'patient5@thaithinh.vn', '1975-06-30', 'Nam', 'Số 54 phố Huế, Hai Bà Trưng, Hà Nội', '0908765432', NULL, NULL, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(6, 11, 'Đỗ Phương Linh', 'patient6@thaithinh.vn', '1992-09-18', 'Nữ', 'Số 12 phố Định Công, Hoàng Mai, Hà Nội', '0965432109', NULL, NULL, '2026-10-25 17:51:38', '2026-10-25 17:51:38');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `personal_access_tokens`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `prescription_items`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `prescription_items`;
CREATE TABLE `prescription_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `prescription_id` bigint(20) unsigned NOT NULL,
  `medicine_id` bigint(20) unsigned NOT NULL,
  `dosage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `frequency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usage` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prescription_items_prescription_id_idx` (`prescription_id`),
  KEY `prescription_items_medicine_id_idx` (`medicine_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `prescription_items` (4 bản ghi)
INSERT INTO `prescription_items` (`id`, `prescription_id`, `medicine_id`, `dosage`, `frequency`, `duration`, `quantity`, `unit`, `usage`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '1 viên/lần', '2 lần/ngày (Sáng - Tối)', '14 ngày', 28, 'Viên', 'Uống sau ăn 30 phút', 'Kiểm tra huyết áp định kỳ mỗi sáng', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(2, 1, 2, '1 viên/lần', '1 lần/ngày (Sáng)', '14 ngày', 14, 'Viên', 'Uống trước ăn sáng', 'Không tự ý ngưng thuốc khi chưa hỏi ý kiến bác sĩ', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(3, 2, 2, '1 viên/lần', '2 lần/ngày (Sáng - Tối)', '14 ngày', 28, 'Viên', 'Uống sau ăn 30 phút', 'Kiểm tra huyết áp định kỳ mỗi sáng', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(4, 2, 3, '1 viên/lần', '1 lần/ngày (Sáng)', '14 ngày', 14, 'Viên', 'Uống trước ăn sáng', 'Không tự ý ngưng thuốc khi chưa hỏi ý kiến bác sĩ', '2026-10-26 16:23:01', '2026-10-26 16:23:01');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `roles`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `roles` (4 bản ghi)
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', NULL, '2026-10-25 17:51:33', '2026-10-25 17:51:33'),
(2, 'doctor', NULL, '2026-10-25 17:51:33', '2026-10-25 17:51:33'),
(3, 'patient', NULL, '2026-10-25 17:51:33', '2026-10-25 17:51:33'),
(4, 'leader', NULL, '2026-10-25 17:51:33', '2026-10-25 17:51:33');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `service_symptoms`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `service_symptoms`;
CREATE TABLE `service_symptoms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint(20) unsigned NOT NULL,
  `symptom_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_symptoms_service_id_foreign` (`service_id`),
  KEY `service_symptoms_symptom_name_index` (`symptom_name`),
  CONSTRAINT `service_symptoms_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `service_symptoms` (21 bản ghi)
INSERT INTO `service_symptoms` (`id`, `service_id`, `symptom_name`, `created_at`, `updated_at`) VALUES
(1, 1, 'Đau tức ngực', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(2, 1, 'Hồi hộp đánh trống ngực', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(3, 1, 'Khó thở', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(4, 2, 'Mệt mỏi kéo dài', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(5, 2, 'Khám sức khỏe định kỳ', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(6, 2, 'Tầm soát bệnh lý tổng quát', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(7, 3, 'Đau bụng thượng vị', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(8, 3, 'Ợ chua trào ngược', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(9, 3, 'Rối loạn tiêu hóa', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(10, 4, 'Ù tai nghe kém', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(11, 4, 'Ngạt mũi mạn tính', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(12, 4, 'Đau rát họng', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(13, 5, 'Trẻ sốt cao', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(14, 5, 'Ho khò khè ở trẻ', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(15, 5, 'Tư vấn dinh dưỡng nhi khoa', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(16, 6, 'Đau đầu chóng mặt', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(17, 6, 'Tăng huyết áp', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(18, 6, 'Tê bì tay chân', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(19, 7, 'Chậm kinh', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(20, 7, 'Siêu âm thai định kỳ', '2026-10-26 16:23:01', '2026-10-26 16:23:01'),
(21, 7, 'Tầm soát sức khỏe phụ khoa', '2026-10-26 16:23:01', '2026-10-26 16:23:01');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `services`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `department_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_department_id_foreign` (`department_id`),
  CONSTRAINT `services_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `services` (7 bản ghi)
INSERT INTO `services` (`id`, `name`, `description`, `image`, `price`, `department_id`, `created_at`, `updated_at`) VALUES
(1, 'Gói khám tim mạch chuyên sâu', 'Khám lâm sàng tim mạch, đo điện tâm đồ ECG và tư vấn chuyên gia.', NULL, '500000.00', 1, '2026-10-25 17:51:33', '2026-10-25 17:51:33'),
(2, 'Điện tâm đồ ECG & Tư vấn chuyên gia', 'Đo điện tâm đồ thế hệ mới và đọc kết quả bởi bác sĩ chuyên khoa tim mạch.', NULL, '250000.00', 1, '2026-10-25 17:51:33', '2026-10-25 17:51:33'),
(3, 'Khám nội soi tai mũi họng kỹ thuật cao', 'Nội soi ống mềm không đau phát hiện sớm viêm tai giữa, viêm xoang, hạt dây thanh.', NULL, '300000.00', 2, '2026-10-25 17:51:33', '2026-10-25 17:51:33'),
(4, 'Gói khám sức khỏe nhi toàn diện', 'Khám tổng quát, đánh giá cột mốc vận động, tư vấn dinh dưỡng và phòng ngừa còi xương.', NULL, '350000.00', 3, '2026-10-25 17:51:33', '2026-10-25 17:51:33'),
(5, 'Gói tầm soát sức khỏe tổng quát', 'Kiểm tra toàn diện các cơ quan nội tạng, chức năng gan thận, đường huyết và huyết áp.', NULL, '600000.00', 4, '2026-10-25 17:51:33', '2026-10-25 17:51:33'),
(6, 'Quản lý tăng huyết áp & Tiểu đường', 'Khám chuyên đề đánh giá hiệu quả thuốc và điều chỉnh phác đồ kiểm soát đường huyết.', NULL, '400000.00', 4, '2026-10-25 17:51:33', '2026-10-25 17:51:33'),
(7, 'Khám thai và siêu âm định kỳ', 'Theo dõi sự phát triển thai nhi, đo tim thai và tư vấn dinh dưỡng thai kỳ.', NULL, '350000.00', 5, '2026-10-25 17:51:33', '2026-10-25 17:51:33');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `test_types`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `test_types`;
CREATE TABLE `test_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `test_types_department_id_foreign` (`department_id`),
  KEY `test_types_created_by_foreign` (`created_by`),
  CONSTRAINT `test_types_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `test_types_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `test_types` (5 bản ghi)
INSERT INTO `test_types` (`id`, `name`, `description`, `department_id`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Xét nghiệm Tổng phân tích tế bào máu ngoại vi', 'Đánh giá 24 thông số tế bào máu', 1, 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(2, 'Xét nghiệm Đường huyết đói (Glucose)', 'Sàng lọc và theo dõi bệnh đái tháo đường', 1, 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(3, 'Xét nghiệm Men gan (AST, ALT)', 'Đánh giá tổn thương tế bào gan', 1, 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(4, 'Xét nghiệm Chức năng thận (Ure, Creatinin)', 'Đánh giá mức độ lọc cầu thận', 1, 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(5, 'Nội soi Tai Mũi Họng ống mềm', 'Khảo sát niêm mạc vòm họng và thanh quản', 2, 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `time_slots`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `time_slots`;
CREATE TABLE `time_slots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `working_schedule_id` bigint(20) unsigned DEFAULT NULL,
  `doctor_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `capacity` int(10) unsigned NOT NULL DEFAULT 1 COMMENT 'Số lượng bệnh nhân tối đa',
  `booked_count` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Số lượng đã đặt',
  `status` enum('available','locked','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doc_date_slot_unique` (`doctor_id`,`date`,`start_time`),
  KEY `time_slots_working_schedule_id_foreign` (`working_schedule_id`),
  KEY `doc_date_status_idx` (`doctor_id`,`date`,`status`),
  KEY `date_status_idx` (`date`,`status`),
  CONSTRAINT `time_slots_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `time_slots_working_schedule_id_foreign` FOREIGN KEY (`working_schedule_id`) REFERENCES `working_schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=421 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `time_slots` (420 bản ghi)
INSERT INTO `time_slots` (`id`, `working_schedule_id`, `doctor_id`, `date`, `start_time`, `end_time`, `capacity`, `booked_count`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-10-25', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(2, 1, 1, '2026-10-25', '08:00:00', '08:30:00', 5, 1, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:40'),
(3, 1, 1, '2026-10-25', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(4, 1, 1, '2026-10-25', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(5, 1, 1, '2026-10-25', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(6, 1, 1, '2026-10-25', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(7, 1, 1, '2026-10-25', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(8, 1, 1, '2026-10-25', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(9, 1, 1, '2026-10-25', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(10, 1, 1, '2026-10-25', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(11, 1, 1, '2026-10-25', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(12, 1, 1, '2026-10-25', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(13, 1, 1, '2026-10-25', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(14, 1, 1, '2026-10-25', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(15, 1, 1, '2026-10-25', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(16, 2, 1, '2026-10-26', '07:30:00', '08:00:00', 1, 1, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:40'),
(17, 2, 1, '2026-10-26', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(18, 2, 1, '2026-10-26', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(19, 2, 1, '2026-10-26', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(20, 2, 1, '2026-10-26', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(21, 2, 1, '2026-10-26', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(22, 2, 1, '2026-10-26', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(23, 2, 1, '2026-10-26', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(24, 2, 1, '2026-10-26', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(25, 2, 1, '2026-10-26', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(26, 2, 1, '2026-10-26', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(27, 2, 1, '2026-10-26', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(28, 2, 1, '2026-10-26', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(29, 2, 1, '2026-10-26', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(30, 2, 1, '2026-10-26', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(31, 3, 1, '2026-10-27', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(32, 3, 1, '2026-10-27', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(33, 3, 1, '2026-10-27', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(34, 3, 1, '2026-10-27', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(35, 3, 1, '2026-10-27', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(36, 3, 1, '2026-10-27', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(37, 3, 1, '2026-10-27', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(38, 3, 1, '2026-10-27', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(39, 3, 1, '2026-10-27', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(40, 3, 1, '2026-10-27', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(41, 3, 1, '2026-10-27', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(42, 3, 1, '2026-10-27', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(43, 3, 1, '2026-10-27', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(44, 3, 1, '2026-10-27', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(45, 3, 1, '2026-10-27', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(46, 4, 1, '2026-10-28', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(47, 4, 1, '2026-10-28', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(48, 4, 1, '2026-10-28', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(49, 4, 1, '2026-10-28', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(50, 4, 1, '2026-10-28', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(51, 4, 1, '2026-10-28', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(52, 4, 1, '2026-10-28', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(53, 4, 1, '2026-10-28', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(54, 4, 1, '2026-10-28', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(55, 4, 1, '2026-10-28', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(56, 4, 1, '2026-10-28', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(57, 4, 1, '2026-10-28', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(58, 4, 1, '2026-10-28', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(59, 4, 1, '2026-10-28', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(60, 4, 1, '2026-10-28', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(61, 5, 1, '2026-10-29', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(62, 5, 1, '2026-10-29', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(63, 5, 1, '2026-10-29', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(64, 5, 1, '2026-10-29', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(65, 5, 1, '2026-10-29', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(66, 5, 1, '2026-10-29', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(67, 5, 1, '2026-10-29', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(68, 5, 1, '2026-10-29', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(69, 5, 1, '2026-10-29', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(70, 5, 1, '2026-10-29', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(71, 5, 1, '2026-10-29', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(72, 5, 1, '2026-10-29', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(73, 5, 1, '2026-10-29', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(74, 5, 1, '2026-10-29', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(75, 5, 1, '2026-10-29', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(76, 6, 1, '2026-10-30', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(77, 6, 1, '2026-10-30', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(78, 6, 1, '2026-10-30', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(79, 6, 1, '2026-10-30', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(80, 6, 1, '2026-10-30', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(81, 6, 1, '2026-10-30', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(82, 6, 1, '2026-10-30', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(83, 6, 1, '2026-10-30', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(84, 6, 1, '2026-10-30', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(85, 6, 1, '2026-10-30', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(86, 6, 1, '2026-10-30', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(87, 6, 1, '2026-10-30', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(88, 6, 1, '2026-10-30', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(89, 6, 1, '2026-10-30', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(90, 6, 1, '2026-10-30', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(91, 7, 1, '2026-10-31', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(92, 7, 1, '2026-10-31', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(93, 7, 1, '2026-10-31', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(94, 7, 1, '2026-10-31', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(95, 7, 1, '2026-10-31', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(96, 7, 1, '2026-10-31', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(97, 7, 1, '2026-10-31', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(98, 7, 1, '2026-10-31', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(99, 7, 1, '2026-10-31', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(100, 7, 1, '2026-10-31', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39');
INSERT INTO `time_slots` (`id`, `working_schedule_id`, `doctor_id`, `date`, `start_time`, `end_time`, `capacity`, `booked_count`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(101, 7, 1, '2026-10-31', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(102, 7, 1, '2026-10-31', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(103, 7, 1, '2026-10-31', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(104, 7, 1, '2026-10-31', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(105, 7, 1, '2026-10-31', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(106, 8, 2, '2026-10-25', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(107, 8, 2, '2026-10-25', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(108, 8, 2, '2026-10-25', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(109, 8, 2, '2026-10-25', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(110, 8, 2, '2026-10-25', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(111, 8, 2, '2026-10-25', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(112, 8, 2, '2026-10-25', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(113, 8, 2, '2026-10-25', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(114, 8, 2, '2026-10-25', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(115, 8, 2, '2026-10-25', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(116, 8, 2, '2026-10-25', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(117, 8, 2, '2026-10-25', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(118, 8, 2, '2026-10-25', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(119, 8, 2, '2026-10-25', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(120, 8, 2, '2026-10-25', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(121, 9, 2, '2026-10-26', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(122, 9, 2, '2026-10-26', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(123, 9, 2, '2026-10-26', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(124, 9, 2, '2026-10-26', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(125, 9, 2, '2026-10-26', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(126, 9, 2, '2026-10-26', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(127, 9, 2, '2026-10-26', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(128, 9, 2, '2026-10-26', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(129, 9, 2, '2026-10-26', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(130, 9, 2, '2026-10-26', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(131, 9, 2, '2026-10-26', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(132, 9, 2, '2026-10-26', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(133, 9, 2, '2026-10-26', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(134, 9, 2, '2026-10-26', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(135, 9, 2, '2026-10-26', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(136, 10, 2, '2026-10-27', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(137, 10, 2, '2026-10-27', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(138, 10, 2, '2026-10-27', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(139, 10, 2, '2026-10-27', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(140, 10, 2, '2026-10-27', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(141, 10, 2, '2026-10-27', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(142, 10, 2, '2026-10-27', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(143, 10, 2, '2026-10-27', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(144, 10, 2, '2026-10-27', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(145, 10, 2, '2026-10-27', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(146, 10, 2, '2026-10-27', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(147, 10, 2, '2026-10-27', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(148, 10, 2, '2026-10-27', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(149, 10, 2, '2026-10-27', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(150, 10, 2, '2026-10-27', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(151, 11, 2, '2026-10-28', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(152, 11, 2, '2026-10-28', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(153, 11, 2, '2026-10-28', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(154, 11, 2, '2026-10-28', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(155, 11, 2, '2026-10-28', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(156, 11, 2, '2026-10-28', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(157, 11, 2, '2026-10-28', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(158, 11, 2, '2026-10-28', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(159, 11, 2, '2026-10-28', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(160, 11, 2, '2026-10-28', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(161, 11, 2, '2026-10-28', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(162, 11, 2, '2026-10-28', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(163, 11, 2, '2026-10-28', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(164, 11, 2, '2026-10-28', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(165, 11, 2, '2026-10-28', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(166, 12, 2, '2026-10-29', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(167, 12, 2, '2026-10-29', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(168, 12, 2, '2026-10-29', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(169, 12, 2, '2026-10-29', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(170, 12, 2, '2026-10-29', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(171, 12, 2, '2026-10-29', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(172, 12, 2, '2026-10-29', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(173, 12, 2, '2026-10-29', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(174, 12, 2, '2026-10-29', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(175, 12, 2, '2026-10-29', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(176, 12, 2, '2026-10-29', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(177, 12, 2, '2026-10-29', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(178, 12, 2, '2026-10-29', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(179, 12, 2, '2026-10-29', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(180, 12, 2, '2026-10-29', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(181, 13, 2, '2026-10-30', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(182, 13, 2, '2026-10-30', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(183, 13, 2, '2026-10-30', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(184, 13, 2, '2026-10-30', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(185, 13, 2, '2026-10-30', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(186, 13, 2, '2026-10-30', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(187, 13, 2, '2026-10-30', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(188, 13, 2, '2026-10-30', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(189, 13, 2, '2026-10-30', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(190, 13, 2, '2026-10-30', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(191, 13, 2, '2026-10-30', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(192, 13, 2, '2026-10-30', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(193, 13, 2, '2026-10-30', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(194, 13, 2, '2026-10-30', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(195, 13, 2, '2026-10-30', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(196, 14, 2, '2026-10-31', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(197, 14, 2, '2026-10-31', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(198, 14, 2, '2026-10-31', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(199, 14, 2, '2026-10-31', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(200, 14, 2, '2026-10-31', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39');
INSERT INTO `time_slots` (`id`, `working_schedule_id`, `doctor_id`, `date`, `start_time`, `end_time`, `capacity`, `booked_count`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(201, 14, 2, '2026-10-31', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(202, 14, 2, '2026-10-31', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(203, 14, 2, '2026-10-31', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(204, 14, 2, '2026-10-31', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(205, 14, 2, '2026-10-31', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(206, 14, 2, '2026-10-31', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(207, 14, 2, '2026-10-31', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(208, 14, 2, '2026-10-31', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(209, 14, 2, '2026-10-31', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(210, 14, 2, '2026-10-31', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(211, 15, 3, '2026-10-25', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(212, 15, 3, '2026-10-25', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(213, 15, 3, '2026-10-25', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(214, 15, 3, '2026-10-25', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(215, 15, 3, '2026-10-25', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(216, 15, 3, '2026-10-25', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(217, 15, 3, '2026-10-25', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(218, 15, 3, '2026-10-25', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(219, 15, 3, '2026-10-25', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(220, 15, 3, '2026-10-25', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(221, 15, 3, '2026-10-25', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(222, 15, 3, '2026-10-25', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(223, 15, 3, '2026-10-25', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(224, 15, 3, '2026-10-25', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(225, 15, 3, '2026-10-25', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(226, 16, 3, '2026-10-26', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(227, 16, 3, '2026-10-26', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(228, 16, 3, '2026-10-26', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(229, 16, 3, '2026-10-26', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(230, 16, 3, '2026-10-26', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(231, 16, 3, '2026-10-26', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(232, 16, 3, '2026-10-26', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(233, 16, 3, '2026-10-26', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(234, 16, 3, '2026-10-26', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(235, 16, 3, '2026-10-26', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(236, 16, 3, '2026-10-26', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(237, 16, 3, '2026-10-26', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(238, 16, 3, '2026-10-26', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(239, 16, 3, '2026-10-26', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(240, 16, 3, '2026-10-26', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(241, 17, 3, '2026-10-27', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(242, 17, 3, '2026-10-27', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(243, 17, 3, '2026-10-27', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(244, 17, 3, '2026-10-27', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(245, 17, 3, '2026-10-27', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(246, 17, 3, '2026-10-27', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(247, 17, 3, '2026-10-27', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(248, 17, 3, '2026-10-27', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(249, 17, 3, '2026-10-27', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(250, 17, 3, '2026-10-27', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(251, 17, 3, '2026-10-27', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(252, 17, 3, '2026-10-27', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(253, 17, 3, '2026-10-27', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(254, 17, 3, '2026-10-27', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(255, 17, 3, '2026-10-27', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(256, 18, 3, '2026-10-28', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(257, 18, 3, '2026-10-28', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(258, 18, 3, '2026-10-28', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(259, 18, 3, '2026-10-28', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(260, 18, 3, '2026-10-28', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(261, 18, 3, '2026-10-28', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(262, 18, 3, '2026-10-28', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(263, 18, 3, '2026-10-28', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(264, 18, 3, '2026-10-28', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(265, 18, 3, '2026-10-28', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(266, 18, 3, '2026-10-28', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(267, 18, 3, '2026-10-28', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(268, 18, 3, '2026-10-28', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(269, 18, 3, '2026-10-28', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(270, 18, 3, '2026-10-28', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(271, 19, 3, '2026-10-29', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(272, 19, 3, '2026-10-29', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(273, 19, 3, '2026-10-29', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(274, 19, 3, '2026-10-29', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(275, 19, 3, '2026-10-29', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(276, 19, 3, '2026-10-29', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(277, 19, 3, '2026-10-29', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(278, 19, 3, '2026-10-29', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(279, 19, 3, '2026-10-29', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(280, 19, 3, '2026-10-29', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(281, 19, 3, '2026-10-29', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(282, 19, 3, '2026-10-29', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(283, 19, 3, '2026-10-29', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(284, 19, 3, '2026-10-29', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(285, 19, 3, '2026-10-29', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(286, 20, 3, '2026-10-30', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(287, 20, 3, '2026-10-30', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(288, 20, 3, '2026-10-30', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(289, 20, 3, '2026-10-30', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(290, 20, 3, '2026-10-30', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(291, 20, 3, '2026-10-30', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(292, 20, 3, '2026-10-30', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(293, 20, 3, '2026-10-30', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(294, 20, 3, '2026-10-30', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(295, 20, 3, '2026-10-30', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(296, 20, 3, '2026-10-30', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(297, 20, 3, '2026-10-30', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(298, 20, 3, '2026-10-30', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(299, 20, 3, '2026-10-30', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(300, 20, 3, '2026-10-30', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40');
INSERT INTO `time_slots` (`id`, `working_schedule_id`, `doctor_id`, `date`, `start_time`, `end_time`, `capacity`, `booked_count`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(301, 21, 3, '2026-10-31', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(302, 21, 3, '2026-10-31', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(303, 21, 3, '2026-10-31', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(304, 21, 3, '2026-10-31', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(305, 21, 3, '2026-10-31', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(306, 21, 3, '2026-10-31', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(307, 21, 3, '2026-10-31', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(308, 21, 3, '2026-10-31', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(309, 21, 3, '2026-10-31', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(310, 21, 3, '2026-10-31', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(311, 21, 3, '2026-10-31', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(312, 21, 3, '2026-10-31', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(313, 21, 3, '2026-10-31', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(314, 21, 3, '2026-10-31', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(315, 21, 3, '2026-10-31', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(316, 22, 4, '2026-10-25', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(317, 22, 4, '2026-10-25', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(318, 22, 4, '2026-10-25', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(319, 22, 4, '2026-10-25', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(320, 22, 4, '2026-10-25', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(321, 22, 4, '2026-10-25', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(322, 22, 4, '2026-10-25', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(323, 22, 4, '2026-10-25', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(324, 22, 4, '2026-10-25', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(325, 22, 4, '2026-10-25', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(326, 22, 4, '2026-10-25', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(327, 22, 4, '2026-10-25', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(328, 22, 4, '2026-10-25', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(329, 22, 4, '2026-10-25', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(330, 22, 4, '2026-10-25', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(331, 23, 4, '2026-10-26', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(332, 23, 4, '2026-10-26', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(333, 23, 4, '2026-10-26', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(334, 23, 4, '2026-10-26', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(335, 23, 4, '2026-10-26', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(336, 23, 4, '2026-10-26', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(337, 23, 4, '2026-10-26', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(338, 23, 4, '2026-10-26', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(339, 23, 4, '2026-10-26', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(340, 23, 4, '2026-10-26', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(341, 23, 4, '2026-10-26', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(342, 23, 4, '2026-10-26', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(343, 23, 4, '2026-10-26', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(344, 23, 4, '2026-10-26', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(345, 23, 4, '2026-10-26', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(346, 24, 4, '2026-10-27', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(347, 24, 4, '2026-10-27', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(348, 24, 4, '2026-10-27', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(349, 24, 4, '2026-10-27', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(350, 24, 4, '2026-10-27', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(351, 24, 4, '2026-10-27', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(352, 24, 4, '2026-10-27', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(353, 24, 4, '2026-10-27', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(354, 24, 4, '2026-10-27', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(355, 24, 4, '2026-10-27', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(356, 24, 4, '2026-10-27', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(357, 24, 4, '2026-10-27', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(358, 24, 4, '2026-10-27', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(359, 24, 4, '2026-10-27', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(360, 24, 4, '2026-10-27', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(361, 25, 4, '2026-10-28', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(362, 25, 4, '2026-10-28', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(363, 25, 4, '2026-10-28', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(364, 25, 4, '2026-10-28', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(365, 25, 4, '2026-10-28', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(366, 25, 4, '2026-10-28', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(367, 25, 4, '2026-10-28', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(368, 25, 4, '2026-10-28', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(369, 25, 4, '2026-10-28', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(370, 25, 4, '2026-10-28', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(371, 25, 4, '2026-10-28', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(372, 25, 4, '2026-10-28', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(373, 25, 4, '2026-10-28', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(374, 25, 4, '2026-10-28', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(375, 25, 4, '2026-10-28', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(376, 26, 4, '2026-10-29', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(377, 26, 4, '2026-10-29', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(378, 26, 4, '2026-10-29', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(379, 26, 4, '2026-10-29', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(380, 26, 4, '2026-10-29', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(381, 26, 4, '2026-10-29', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(382, 26, 4, '2026-10-29', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(383, 26, 4, '2026-10-29', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(384, 26, 4, '2026-10-29', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(385, 26, 4, '2026-10-29', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(386, 26, 4, '2026-10-29', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(387, 26, 4, '2026-10-29', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(388, 26, 4, '2026-10-29', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(389, 26, 4, '2026-10-29', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(390, 26, 4, '2026-10-29', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(391, 27, 4, '2026-10-30', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(392, 27, 4, '2026-10-30', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(393, 27, 4, '2026-10-30', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(394, 27, 4, '2026-10-30', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(395, 27, 4, '2026-10-30', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(396, 27, 4, '2026-10-30', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(397, 27, 4, '2026-10-30', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(398, 27, 4, '2026-10-30', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(399, 27, 4, '2026-10-30', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(400, 27, 4, '2026-10-30', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40');
INSERT INTO `time_slots` (`id`, `working_schedule_id`, `doctor_id`, `date`, `start_time`, `end_time`, `capacity`, `booked_count`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(401, 27, 4, '2026-10-30', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(402, 27, 4, '2026-10-30', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(403, 27, 4, '2026-10-30', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(404, 27, 4, '2026-10-30', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(405, 27, 4, '2026-10-30', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(406, 28, 4, '2026-10-31', '07:30:00', '08:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(407, 28, 4, '2026-10-31', '08:00:00', '08:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(408, 28, 4, '2026-10-31', '08:30:00', '09:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(409, 28, 4, '2026-10-31', '09:00:00', '09:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(410, 28, 4, '2026-10-31', '09:30:00', '10:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(411, 28, 4, '2026-10-31', '10:00:00', '10:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(412, 28, 4, '2026-10-31', '10:30:00', '11:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(413, 28, 4, '2026-10-31', '11:00:00', '11:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(414, 28, 4, '2026-10-31', '13:30:00', '14:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(415, 28, 4, '2026-10-31', '14:00:00', '14:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(416, 28, 4, '2026-10-31', '14:30:00', '15:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(417, 28, 4, '2026-10-31', '15:00:00', '15:30:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(418, 28, 4, '2026-10-31', '15:30:00', '16:00:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(419, 28, 4, '2026-10-31', '16:00:00', '16:30:00', 5, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(420, 28, 4, '2026-10-31', '16:30:00', '17:00:00', 1, 0, 'available', 1, '2026-10-25 17:51:40', '2026-10-25 17:51:40');

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `users`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL DEFAULT 1,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insurance_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `users` (11 bản ghi)
INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `phone`, `insurance_number`, `birthdate`, `gender`, `address`) VALUES
(1, 1, 'Quản trị viên Thái Thịnh', 'admin@thaithinh.vn', '2026-10-25 17:51:33', '$2y$12$zijDn.FNDtve5dXdi3nszuseyFBNPjW4q3ued8JYpr33Kk9HbvHUm', NULL, '2026-10-25 17:51:33', '2026-10-25 17:51:33', NULL, NULL, NULL, NULL, NULL),
(2, 2, 'BS. CKII Nguyễn Văn Hùng', 'hung.nguyen@thaithinh.vn', '2026-10-25 17:51:34', '$2y$12$63Urt/hCtgXyOpFFgEoZ0.IcNGJ4hv1fSgpIl90QQGuCm6yEnruvK', NULL, '2026-10-25 17:51:34', '2026-10-25 17:51:34', '0912111001', NULL, NULL, NULL, NULL),
(3, 2, 'ThS. BS Trần Thị Mai', 'mai.tran@thaithinh.vn', '2026-10-25 17:51:34', '$2y$12$BBCr5lZToz3/m8P7Kq5m0egK8ki.IJGzZ1GPb61z.G0vZeERmeADm', NULL, '2026-10-25 17:51:34', '2026-10-25 17:51:34', '0912111002', NULL, NULL, NULL, NULL),
(4, 2, 'BSCK I Lê Hoàng Nam', 'nam.le@thaithinh.vn', '2026-10-25 17:51:35', '$2y$12$MXuySQ6G/.aOLMKcmffq3uP.LHqywea5jXImpcqQUFh9LRGD1xatW', NULL, '2026-10-25 17:51:35', '2026-10-25 17:51:35', '0912111003', NULL, NULL, NULL, NULL),
(5, 2, 'BSCK II Phạm Thị Thu Hương', 'huong.pham@thaithinh.vn', '2026-10-25 17:51:35', '$2y$12$mW5Mda4L1vf0HP/VWiRtT.9fajl7P7zCHAu3BQtVLP7hX./0RvaMO', NULL, '2026-10-25 17:51:35', '2026-10-25 17:51:35', '0912111004', NULL, NULL, NULL, NULL),
(6, 3, 'Bùi Tuấn Anh', 'patient1@thaithinh.vn', '2026-10-25 17:51:36', '$2y$12$09Kyljif5NvqCz9ctoalI.vEsbfcPdogv53Ce5UVFohLmV69tNivO', NULL, '2026-10-25 17:51:36', '2026-10-25 17:51:36', '0987654321', NULL, NULL, NULL, NULL),
(7, 3, 'Vũ Thị Hồng Nhung', 'patient2@thaithinh.vn', '2026-10-25 17:51:36', '$2y$12$qKv/RHkK25CMktBKq1eEJ.YraLm36Jy0gOYtnc.HsR/zK7pJODnq6', NULL, '2026-10-25 17:51:36', '2026-10-25 17:51:36', '0912345678', NULL, NULL, NULL, NULL),
(8, 3, 'Nguyễn Minh Quang', 'patient3@thaithinh.vn', '2026-10-25 17:51:37', '$2y$12$qZESSd8nFIkCTyDsf6GwsOWs03YBo.DS8ApfE.Kp05HedpuqoM23u', NULL, '2026-10-25 17:51:37', '2026-10-25 17:51:37', '0934567890', NULL, NULL, NULL, NULL),
(9, 3, 'Lê Thu Trang', 'patient4@thaithinh.vn', '2026-10-25 17:51:37', '$2y$12$2rfZRAJKDI6SUn.7w7KmQe0IDNtmicqHSzQibJQchWTrd8CE8aBsi', NULL, '2026-10-25 17:51:37', '2026-10-25 17:51:37', '0976543210', NULL, NULL, NULL, NULL),
(10, 3, 'Hoàng Đức Trọng', 'patient5@thaithinh.vn', '2026-10-25 17:51:38', '$2y$12$z..1Nsvd8ZKmHk/bJvre6ecK5PC5w/mgCTO74Erc8OhOd.BtJ/.sK', NULL, '2026-10-25 17:51:38', '2026-10-25 17:51:38', '0908765432', NULL, NULL, NULL, NULL),
(11, 3, 'Đỗ Phương Linh', 'patient6@thaithinh.vn', '2026-10-25 17:51:38', '$2y$12$kgDsA0e43NBZFIx7Ze3NMeyDC5XjG1NuWxvtaa4DeIrQBO6xnUtfi', NULL, '2026-10-25 17:51:38', '2026-10-25 17:51:38', '0965432109', NULL, NULL, NULL, NULL);

-- ----------------------------------------------------------------------------
-- Cấu trúc bảng cho bảng `working_schedules`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `working_schedules`;
CREATE TABLE `working_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `doctor_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `day_of_week` tinyint(4) DEFAULT NULL COMMENT '0: CN, 1: T2, ..., 6: T7',
  `start_time` time NOT NULL DEFAULT '07:30:00',
  `end_time` time NOT NULL DEFAULT '17:00:00',
  `status` enum('active','inactive','on_leave') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `note` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `working_schedules_date_status_index` (`date`,`status`),
  KEY `working_schedules_doctor_id_idx` (`doctor_id`),
  KEY `working_schedules_doc_date_idx` (`doctor_id`,`date`),
  CONSTRAINT `working_schedules_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đang đổ dữ liệu cho bảng `working_schedules` (28 bản ghi)
INSERT INTO `working_schedules` (`id`, `doctor_id`, `date`, `day_of_week`, `start_time`, `end_time`, `status`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-10-25', 0, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(2, 1, '2026-10-26', 1, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(3, 1, '2026-10-27', 2, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:38', '2026-10-25 17:51:38'),
(4, 1, '2026-10-28', 3, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(5, 1, '2026-10-29', 4, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(6, 1, '2026-10-30', 5, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(7, 1, '2026-10-31', 6, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(8, 2, '2026-10-25', 0, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(9, 2, '2026-10-26', 1, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(10, 2, '2026-10-27', 2, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(11, 2, '2026-10-28', 3, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(12, 2, '2026-10-29', 4, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(13, 2, '2026-10-30', 5, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(14, 2, '2026-10-31', 6, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(15, 3, '2026-10-25', 0, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(16, 3, '2026-10-26', 1, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(17, 3, '2026-10-27', 2, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(18, 3, '2026-10-28', 3, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(19, 3, '2026-10-29', 4, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:39', '2026-10-25 17:51:39'),
(20, 3, '2026-10-30', 5, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(21, 3, '2026-10-31', 6, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(22, 4, '2026-10-25', 0, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(23, 4, '2026-10-26', 1, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(24, 4, '2026-10-27', 2, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(25, 4, '2026-10-28', 3, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(26, 4, '2026-10-29', 4, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(27, 4, '2026-10-30', 5, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:40', '2026-10-25 17:51:40'),
(28, 4, '2026-10-31', 6, '07:30:00', '17:00:00', 'active', 'Ca làm việc thường quy tại phòng khám', '2026-10-25 17:51:40', '2026-10-25 17:51:40');

SET FOREIGN_KEY_CHECKS = 1;
