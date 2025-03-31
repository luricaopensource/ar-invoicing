-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 06-08-2018 a las 08:31:00
-- Versión del servidor: 10.1.26-MariaDB-0+deb9u1
-- Versión de PHP: 7.0.30-0+deb9u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `invoicing`
--

-- Basic login
CREATE TABLE `session` (
  `ip`        varchar(16 ) NOT NULL DEFAULT '0' COMMENT 'IP',
  `last_time` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ultima Fecha',
  `store`     varchar(255) NOT NULL DEFAULT '0' COMMENT 'Almacen',
  `id_user`   bigint (20 ) NOT NULL COMMENT 'Id Usuario',
  `type`      varchar(255) NOT NULL COMMENT 'Tipo',
  `cookie`    varchar(255) NOT NULL COMMENT 'Cookie',
  `expire`    int    (11 ) NOT NULL COMMENT 'Expiracion',
  `login_at`  datetime     NOT NULL COMMENT 'Fecha Login'
) ENGINE=MEMORY DEFAULT CHARSET=utf8;


CREATE TABLE `usuarios` (
  `id`               int    (11 ) NOT NULL AUTO_INCREMENT,
  `nombre`           varchar(255) NOT NULL,
  `apellido`         varchar(255) NOT NULL,
  `tipo`             int    (11 ) NOT NULL,
  `user`             varchar(255) NOT NULL,
  `pass`             varchar(255) NOT NULL,
  `mail`             varchar(255) NOT NULL,
  `tel`              varchar(255) NOT NULL, 
  `id_empresa`       int    (11 ) NOT NULL,
  `id_user_delegado` int    (11 ) NOT NULL,
  `id_distribuidora` int    (11 ) NOT NULL,
  `puede_crear`      tinyint(1  ) NOT NULL,
  `activo`           tinyint(1  ) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `usuarios_tipo` (
  `id`         int    (11 ) NOT NULL AUTO_INCREMENT,
  `nombre`     varchar(255) NOT NULL,
  `color`      varchar(255) NOT NULL,
  `dashboard`  varchar(255) NOT NULL,
  `dashcenter` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;