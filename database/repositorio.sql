-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2026 at 03:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `repositorio`
--

-- --------------------------------------------------------

--
-- Table structure for table `auditorias`
--

CREATE TABLE `auditorias` (
  `aud_codigo` bigint(20) NOT NULL,
  `pry_codigo` bigint(20) NOT NULL,
  `aud_accion` varchar(255) NOT NULL,
  `aud_modulo` varchar(255) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `aud_user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `componentes`
--

CREATE TABLE `componentes` (
  `comp_codigo` bigint(20) UNSIGNED NOT NULL,
  `comp_nombre` varchar(255) NOT NULL,
  `coord_codigo` bigint(20) UNSIGNED DEFAULT NULL,
  `comp_anio` varchar(32) DEFAULT NULL,
  `comp_es_obligatorio` tinyint(1) NOT NULL DEFAULT 1,
  `comp_estado_logico` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `componentes`
--

INSERT INTO `componentes` (`comp_codigo`, `comp_nombre`, `coord_codigo`, `comp_anio`, `comp_es_obligatorio`, `comp_estado_logico`, `created_at`, `updated_at`) VALUES
(1, 'GESTION', 4, 'IV', 1, 1, '2026-05-26 02:35:25', '2026-05-26 02:35:25');

-- --------------------------------------------------------

--
-- Table structure for table `comunidades`
--

CREATE TABLE `comunidades` (
  `com_codigo` bigint(20) NOT NULL,
  `com_nombre` varchar(255) NOT NULL,
  `com_direccion` text DEFAULT NULL,
  `com_rif` varchar(255) DEFAULT NULL,
  `com_correo` varchar(255) DEFAULT NULL,
  `com_numero_telefono` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `anio` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comunidad_estudiante`
--

CREATE TABLE `comunidad_estudiante` (
  `ces_codigo` bigint(20) NOT NULL,
  `com_codigo` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coordinaciones`
--

CREATE TABLE `coordinaciones` (
  `coord_codigo` bigint(20) UNSIGNED NOT NULL,
  `coord_nombre` varchar(255) NOT NULL,
  `coord_descripcion` text DEFAULT NULL,
  `coord_activo` tinyint(1) NOT NULL DEFAULT 1,
  `coord_alertar_comunidades` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coordinaciones`
--

INSERT INTO `coordinaciones` (`coord_codigo`, `coord_nombre`, `coord_descripcion`, `coord_activo`, `coord_alertar_comunidades`, `created_at`, `updated_at`) VALUES
(1, 'INFORMÁTICA', 'PNF INFORMÁTICA', 1, 0, '2026-05-25 09:47:21', '2026-05-25 09:47:21'),
(2, 'AGROALIMENTACIÓN', 'PNF AGROALIMENTACIÓN', 1, 0, '2026-05-25 09:47:21', '2026-05-25 09:47:21');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grupo_proyecto_modulo`
--

CREATE TABLE `grupo_proyecto_modulo` (
  `grp_codigo` bigint(20) UNSIGNED NOT NULL,
  `grp_nombre` varchar(120) NOT NULL,
  `grp_contexto` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`grp_contexto`)),
  `grp_creador_cedula` varchar(20) DEFAULT NULL,
  `grp_com_codigo` bigint(20) UNSIGNED DEFAULT NULL,
  `grp_miembros` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grupo_proyecto_modulo`
--

INSERT INTO `grupo_proyecto_modulo` (`grp_codigo`, `grp_nombre`, `grp_contexto`, `grp_creador_cedula`, `grp_com_codigo`, `grp_miembros`, `created_at`, `updated_at`) VALUES
(1, 'grupo repositorio', '{\"lap_codigo\":72,\"sec_codigo\":11587,\"pro_codigo\":4}', '30966221', NULL, '[{\"cedula\":\"31306741\",\"rol_id\":2,\"nombre\":\"FERNANDO ANTONIO\",\"apellido\":\"LEON LANDER\"},{\"cedula\":\"31057795\",\"rol_id\":2,\"nombre\":\"JHOEL ALEXIS\",\"apellido\":\"LUGO MARTINEZ\"},{\"cedula\":\"30966221\",\"rol_id\":2,\"nombre\":\"EMANUEL ISAI\",\"apellido\":\"PERAZA GONZALEZ\"},{\"cedula\":\"31490175\",\"rol_id\":1,\"nombre\":\"MARIA FERNANDA\",\"apellido\":\"PEREIRA BARCO\"},{\"cedula\":\"30638064\",\"rol_id\":2,\"nombre\":\"MARIELIS NAYARIT\",\"apellido\":\"MARQUEZ AVILA\"},{\"cedula\":\"31553458\",\"rol_id\":2,\"nombre\":\"MARIELIS ALEJANDRA\",\"apellido\":\"HERNANDEZ MOYEJA\"}]', '2026-05-25 09:35:33', '2026-05-26 14:51:05');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) NOT NULL,
  `reserved_at` int(10) DEFAULT NULL,
  `available_at` int(10) NOT NULL,
  `created_at` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

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
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `linea_investigacions`
--

CREATE TABLE `linea_investigacions` (
  `lin_codigo` bigint(20) NOT NULL,
  `lin_nombre_investigacion` varchar(255) NOT NULL,
  `lin_descripcion` text DEFAULT NULL,
  `lin_area_de_investigacion` varchar(255) NOT NULL,
  `coord_codigo` bigint(20) UNSIGNED DEFAULT NULL,
  `lin_estado` enum('Activo','Inactivo') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `metodologia_investigacions`
--

CREATE TABLE `metodologia_investigacions` (
  `mei_codigo` bigint(20) NOT NULL,
  `mei_nombre` varchar(255) NOT NULL,
  `mei_descripcion` text DEFAULT NULL,
  `mei_estado_logico` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proyectos`
--

CREATE TABLE `proyectos` (
  `pry_codigo` bigint(20) NOT NULL,
  `pry_titulo` varchar(255) NOT NULL,
  `com_codigo` bigint(20) DEFAULT NULL,
  `aud_codigo` bigint(20) DEFAULT NULL,
  `pry_resumen` text NOT NULL,
  `pry_fecha_subida` date NOT NULL,
  `pry_asignacion_ct` tinyint(4) NOT NULL DEFAULT 0,
  `pry_calificacion` tinyint(4) DEFAULT NULL,
  `pry_fecha_aprobacion` date DEFAULT NULL,
  `pry_direccion_logica` varchar(255) DEFAULT NULL,
  `pry_archivo_path` varchar(255) DEFAULT NULL,
  `lin_codigo` bigint(20) NOT NULL,
  `mei_codigo` bigint(20) NOT NULL,
  `tpu_codigo` bigint(20) NOT NULL,
  `tin_codigo` bigint(20) NOT NULL,
  `pry_estado_logico` tinyint(1) NOT NULL DEFAULT 1,
  `pry_estado_` enum('Pendiente','Aprobado','Rechazado') DEFAULT NULL,
  `pry_motivo_rechazo` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3cxH8OCE6lOCxVZ6Dd1vBfSeGuq8JeJMox9eyu4C', 31306741, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0 (Edition std-2)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiT0hVa2d2eFBlSXUzMEQ1SVBWclRaSmg5TjlldnNmSTNoTHRyTTE0eiI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO3M6MTI6IjMxMzA2NzQxICAgICI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTM5OiJodHRwOi8vbG9jYWxob3N0L3JlcG9zaXRvcmlvL3JlcG9zaXRvcmlvX3BydWViYS9zaXN0ZW1hL3B1YmxpYy9tYWdpYy1sb2dpbi9ORFRnS0M2MmJXdGxEVFJGbE1TQW1lMU01YVJFaU1rU1lVT2pYNk9XRTk4cDdsdW9Wbm1xUk1aWEZYeE5ocW5GIjtzOjU6InJvdXRlIjtzOjExOiJtYWdpYy5sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1779632317),
('q7lkRoorjVyBVN9vvCsQzI5gzgi44w8OXEspb3bD', 31306741, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0 (Edition std-2)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTHBzTnVrU3hSYmFoc0FiU0MyZWowUm8yanJWYUZrZGtVOE5xcmVieSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTc3OiJodHRwOi8vbG9jYWxob3N0L3JlcG9zaXRvcmlvL3JlcG9zaXRvcmlvX3BydWViYS9zaXN0ZW1hL3B1YmxpYy9tYWdpYy1sb2dpbi8zMTMwNjc0MT9leHBpcmVzPTE3Nzk3MTE0Nzgmc2lnbmF0dXJlPTg5NWRjMTE1ZTM1NGVkODczYzdmNTFmNDdlMDNhODU0YmNjOGU1NThmYjhlODA1MDliMDBjMzY2ZWQwNjBiNjIiO3M6NToicm91dGUiO3M6MTE6Im1hZ2ljLmxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO3M6MTI6IjMxMzA2NzQxICAgICI7fQ==', 1779625087);

-- --------------------------------------------------------

--
-- Table structure for table `tipo_investigacions`
--

CREATE TABLE `tipo_investigacions` (
  `tin_codigo` bigint(20) NOT NULL,
  `tin_nombre` varchar(255) NOT NULL,
  `tin_descripcion` text DEFAULT NULL,
  `tin_estado_logico` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipo_publicacions`
--

CREATE TABLE `tipo_publicacions` (
  `tpu_codigo` bigint(20) NOT NULL,
  `tpu_nombre` varchar(255) NOT NULL,
  `tpu_mencion_honorifica` tinyint(4) NOT NULL DEFAULT 0,
  `tpu_estado_logico` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auditorias`
--
ALTER TABLE `auditorias`
  ADD PRIMARY KEY (`aud_codigo`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `componentes`
--
ALTER TABLE `componentes`
  ADD PRIMARY KEY (`comp_codigo`);

--
-- Indexes for table `comunidades`
--
ALTER TABLE `comunidades`
  ADD PRIMARY KEY (`com_codigo`);

--
-- Indexes for table `comunidad_estudiante`
--
ALTER TABLE `comunidad_estudiante`
  ADD PRIMARY KEY (`ces_codigo`),
  ADD KEY `comunidad_estudiante_comunidad_id_foreign` (`com_codigo`);

--
-- Indexes for table `coordinaciones`
--
ALTER TABLE `coordinaciones`
  ADD PRIMARY KEY (`coord_codigo`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grupo_proyecto_modulo`
--
ALTER TABLE `grupo_proyecto_modulo`
  ADD PRIMARY KEY (`grp_codigo`),
  ADD KEY `grupo_proyecto_modulo_grp_creador_cedula_index` (`grp_creador_cedula`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `linea_investigacions`
--
ALTER TABLE `linea_investigacions`
  ADD PRIMARY KEY (`lin_codigo`);

--
-- Indexes for table `metodologia_investigacions`
--
ALTER TABLE `metodologia_investigacions`
  ADD PRIMARY KEY (`mei_codigo`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`pry_codigo`),
  ADD KEY `proyectos_linea_investigacion_id_foreign` (`lin_codigo`),
  ADD KEY `proyectos_metodologia_id_foreign` (`mei_codigo`),
  ADD KEY `proyectos_tipo_investigacion_id_foreign` (`tin_codigo`),
  ADD KEY `proyectos_tipo_publicacion_id_foreign` (`tpu_codigo`),
  ADD KEY `aud_codigo` (`aud_codigo`),
  ADD KEY `com_codigo` (`com_codigo`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tipo_investigacions`
--
ALTER TABLE `tipo_investigacions`
  ADD PRIMARY KEY (`tin_codigo`);

--
-- Indexes for table `tipo_publicacions`
--
ALTER TABLE `tipo_publicacions`
  ADD PRIMARY KEY (`tpu_codigo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auditorias`
--
ALTER TABLE `auditorias`
  MODIFY `aud_codigo` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `componentes`
--
ALTER TABLE `componentes`
  MODIFY `comp_codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `comunidades`
--
ALTER TABLE `comunidades`
  MODIFY `com_codigo` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comunidad_estudiante`
--
ALTER TABLE `comunidad_estudiante`
  MODIFY `ces_codigo` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coordinaciones`
--
ALTER TABLE `coordinaciones`
  MODIFY `coord_codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `grupo_proyecto_modulo`
--
ALTER TABLE `grupo_proyecto_modulo`
  MODIFY `grp_codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `linea_investigacions`
--
ALTER TABLE `linea_investigacions`
  MODIFY `lin_codigo` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `metodologia_investigacions`
--
ALTER TABLE `metodologia_investigacions`
  MODIFY `mei_codigo` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `pry_codigo` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipo_investigacions`
--
ALTER TABLE `tipo_investigacions`
  MODIFY `tin_codigo` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipo_publicacions`
--
ALTER TABLE `tipo_publicacions`
  MODIFY `tpu_codigo` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comunidad_estudiante`
--
ALTER TABLE `comunidad_estudiante`
  ADD CONSTRAINT `comunidad_estudiante_comunidad_id_foreign` FOREIGN KEY (`com_codigo`) REFERENCES `comunidades` (`com_codigo`) ON DELETE CASCADE;

--
-- Constraints for table `proyectos`
--
ALTER TABLE `proyectos`
  ADD CONSTRAINT `proyectos_ibfk_1` FOREIGN KEY (`aud_codigo`) REFERENCES `auditorias` (`aud_codigo`),
  ADD CONSTRAINT `proyectos_ibfk_2` FOREIGN KEY (`com_codigo`) REFERENCES `comunidades` (`com_codigo`),
  ADD CONSTRAINT `proyectos_linea_investigacion_id_foreign` FOREIGN KEY (`lin_codigo`) REFERENCES `linea_investigacions` (`lin_codigo`),
  ADD CONSTRAINT `proyectos_metodologia_id_foreign` FOREIGN KEY (`mei_codigo`) REFERENCES `metodologia_investigacions` (`mei_codigo`),
  ADD CONSTRAINT `proyectos_tipo_investigacion_id_foreign` FOREIGN KEY (`tin_codigo`) REFERENCES `tipo_investigacions` (`tin_codigo`),
  ADD CONSTRAINT `proyectos_tipo_publicacion_id_foreign` FOREIGN KEY (`tpu_codigo`) REFERENCES `tipo_publicacions` (`tpu_codigo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
