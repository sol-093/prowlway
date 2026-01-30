-- Seed: PH (Philippines) + Dasma (Dasmariñas) holidays for Institute calendar
-- Run after migration_create_holidays.sql
-- Fixed PH holidays + Dasmariñas Foundation Day (Nov 26) per year

INSERT INTO `holidays` (`date`, `name`, `type`, `region`) VALUES
-- 2024
('2024-01-01', 'New Year\'s Day', 'regular', 'PH'),
('2024-02-25', 'EDSA Revolution Anniversary', 'special_working', 'PH'),
('2024-04-09', 'Araw ng Kagitingan', 'regular', 'PH'),
('2024-05-01', 'Labor Day', 'regular', 'PH'),
('2024-06-12', 'Independence Day', 'regular', 'PH'),
('2024-08-21', 'Ninoy Aquino Day', 'special_non_working', 'PH'),
('2024-08-26', 'National Heroes Day', 'regular', 'PH'),
('2024-11-26', 'Foundation Day of Dasmariñas City', 'special_non_working', 'Dasma'),
('2024-11-30', 'Bonifacio Day', 'regular', 'PH'),
('2024-12-25', 'Christmas Day', 'regular', 'PH'),
('2024-12-30', 'Rizal Day', 'regular', 'PH'),
('2024-12-31', 'Last Day of the Year', 'special_non_working', 'PH'),
-- 2025
('2025-01-01', 'New Year\'s Day', 'regular', 'PH'),
('2025-02-25', 'EDSA Revolution Anniversary', 'special_working', 'PH'),
('2025-04-18', 'Good Friday', 'regular', 'PH'),
('2025-04-09', 'Araw ng Kagitingan', 'regular', 'PH'),
('2025-05-01', 'Labor Day', 'regular', 'PH'),
('2025-06-12', 'Independence Day', 'regular', 'PH'),
('2025-08-21', 'Ninoy Aquino Day', 'special_non_working', 'PH'),
('2025-08-25', 'National Heroes Day', 'regular', 'PH'),
('2025-11-26', 'Foundation Day of Dasmariñas City', 'special_non_working', 'Dasma'),
('2025-11-30', 'Bonifacio Day', 'regular', 'PH'),
('2025-12-25', 'Christmas Day', 'regular', 'PH'),
('2025-12-30', 'Rizal Day', 'regular', 'PH'),
('2025-12-31', 'Last Day of the Year', 'special_non_working', 'PH'),
-- 2026
('2026-01-01', 'New Year\'s Day', 'regular', 'PH'),
('2026-02-25', 'EDSA Revolution Anniversary', 'special_working', 'PH'),
('2026-04-02', 'Maundy Thursday', 'regular', 'PH'),
('2026-04-03', 'Good Friday', 'regular', 'PH'),
('2026-04-04', 'Black Saturday', 'special_non_working', 'PH'),
('2026-04-09', 'Araw ng Kagitingan', 'regular', 'PH'),
('2026-05-01', 'Labor Day', 'regular', 'PH'),
('2026-06-12', 'Independence Day', 'regular', 'PH'),
('2026-08-21', 'Ninoy Aquino Day', 'special_non_working', 'PH'),
('2026-08-31', 'National Heroes Day', 'regular', 'PH'),
('2026-11-26', 'Foundation Day of Dasmariñas City', 'special_non_working', 'Dasma'),
('2026-11-30', 'Bonifacio Day', 'regular', 'PH'),
('2026-12-25', 'Christmas Day', 'regular', 'PH'),
('2026-12-30', 'Rizal Day', 'regular', 'PH'),
('2026-12-31', 'Last Day of the Year', 'special_non_working', 'PH');
