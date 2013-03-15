-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 15, 2013 at 11:11 AM
-- Server version: 5.5.25
-- PHP Version: 5.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `mood_data`
--

-- --------------------------------------------------------

--
-- Table structure for table `bbc_episodes`
--

CREATE TABLE `bbc_episodes` (
  `pid` varchar(8) NOT NULL,
  `parent_pid` varchar(8) NOT NULL,
  `date` datetime NOT NULL,
  `synopsis` varchar(512) NOT NULL,
  `angry` int(11) NOT NULL DEFAULT '0',
  `excited` int(11) NOT NULL DEFAULT '0',
  `happy` int(11) NOT NULL DEFAULT '0',
  `relaxing` int(11) NOT NULL DEFAULT '0',
  `sad` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pid`),
  KEY `parent_pid` (`parent_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bbc_programmes`
--

CREATE TABLE `bbc_programmes` (
  `pid` char(8) NOT NULL,
  `title` varchar(128) NOT NULL,
  `service_key` varchar(32) NOT NULL,
  `service_id` varchar(32) NOT NULL,
  `service_name` varchar(64) NOT NULL,
  `image` varchar(128) NOT NULL,
  `angry` int(11) NOT NULL DEFAULT '0',
  `excited` int(11) NOT NULL DEFAULT '0',
  `happy` int(11) NOT NULL DEFAULT '0',
  `relaxing` int(11) NOT NULL DEFAULT '0',
  `sad` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bbc_segments`
--

CREATE TABLE `bbc_segments` (
  `pid` varchar(8) NOT NULL,
  `episode_pid` varchar(8) NOT NULL,
  `artist` varchar(512) NOT NULL,
  `track` varchar(512) NOT NULL,
  `artist_echonest_id` varchar(128) NOT NULL,
  `angry` int(11) NOT NULL DEFAULT '0',
  `excited` int(11) NOT NULL DEFAULT '0',
  `happy` int(11) NOT NULL DEFAULT '0',
  `relaxing` int(11) NOT NULL DEFAULT '0',
  `sad` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pid`),
  KEY `episode_pid` (`episode_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
