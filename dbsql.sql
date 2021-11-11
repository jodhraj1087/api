
CREATE TABLE `apiusers` (
  `id` int(11) NOT NULL,
  `name` varchar(155) DEFAULT NULL,
  `user_name` varchar(20) NOT NULL,
  `password` varchar(60) NOT NULL,
  `avatar` varchar(60) DEFAULT NULL,
  `email` varchar(155) DEFAULT NULL,
  `user_role` enum('admin','user') NOT NULL DEFAULT 'admin',
  `confirmpin` int(11) DEFAULT NULL,
  `isconfirmed` tinyint(1) NOT NULL DEFAULT '0',
  `registered_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apiusers`
--
ALTER TABLE `apiusers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `apiusers`
--
ALTER TABLE `apiusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
