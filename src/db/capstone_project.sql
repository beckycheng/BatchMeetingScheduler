-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2022-04-25 08:45:29
-- 伺服器版本： 10.4.20-MariaDB
-- PHP 版本： 8.0.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫: `capstone_project`
--

-- --------------------------------------------------------

--
-- 資料表結構 `choose`
--

CREATE TABLE `choose` (
  `id` int(11) NOT NULL,
  `studentid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `choose1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `choose2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `choose3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `choose4` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `choose5` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `choose6` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `choose7` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `choose8` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `choose9` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `choose10` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `meeting`
--

CREATE TABLE `meeting` (
  `id` int(11) NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teacher` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` int(11) NOT NULL,
  `deadline` datetime NOT NULL,
  `timeslots` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`timeslots`)),
  `studentid` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`studentid`)),
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `result`
--

CREATE TABLE `result` (
  `id` int(11) NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`result`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `choose`
--
ALTER TABLE `choose`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `meeting`
--
ALTER TABLE `meeting`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `result`
--
ALTER TABLE `result`
  ADD PRIMARY KEY (`id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `choose`
--
ALTER TABLE `choose`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `meeting`
--
ALTER TABLE `meeting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `result`
--
ALTER TABLE `result`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
