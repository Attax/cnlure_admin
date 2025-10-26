-- 用户数据冷启动SQL脚本
-- 目标：设置auto_increment=100000并插入ID<100000的记录

-- 首先备份当前auto_increment值（可选）
SET @current_auto_increment = (SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user');
SELECT CONCAT('当前auto_increment值: ', @current_auto_increment) AS message;

-- 设置表的auto_increment为100001
ALTER TABLE user AUTO_INCREMENT = 100001;
SELECT '已设置auto_increment=100001' AS message;

-- 方法1：使用存储过程批量插入（推荐用于大量数据）
DELIMITER //

DROP PROCEDURE IF EXISTS insert_users_batch //

CREATE PROCEDURE insert_users_batch(IN start_id INT, IN end_id INT)
BEGIN
    DECLARE current_id INT DEFAULT start_id;
    DECLARE batch_size INT DEFAULT 1000; -- 每批插入的记录数
    DECLARE inserted_count INT DEFAULT 0;
    
    -- 记录开始时间
    SET @start_time = NOW();
    
    -- 批量插入数据
    WHILE current_id <= end_id DO
        -- 临时表用于存储一批数据
        DROP TEMPORARY TABLE IF EXISTS temp_users;
        CREATE TEMPORARY TABLE temp_users LIKE user;
        
        -- 生成当前批次的数据
        WHILE current_id <= end_id AND inserted_count < batch_size DO
            -- 插入数据到临时表
            INSERT INTO temp_users (
                id, nickname, username, password, account_type, 
                country_code, phone, mail, avatar, verified, 
                signature, member_type, is_oauth, follower_count, 
                follow_count, status, created_at, updated_at, type
            ) VALUES (
                current_id, 
                CONCAT('用户', current_id),
                '', -- username为空
                NULL, -- 根据要求不生成密码
                FLOOR(RAND() * 3) + 1, -- 1-3的随机账号类型
                86,
                CONCAT('138', LPAD(current_id, 8, '0')), -- 生成唯一的手机号
                CONCAT('user', current_id, '@example.com'),
                CONCAT('https://placehold.co/400?text=', current_id, '&font=roboto'), -- 使用占位图片，text为用户ID
                FLOOR(RAND() * 2), -- 0或1的实名认证状态
                CONCAT('这是用户', current_id, '的个性签名'),
                FLOOR(RAND() * 3), -- 0-2的会员类型
                0,
                FLOOR(RAND() * 1000), -- 0-999的随机粉丝数
                FLOOR(RAND() * 500), -- 0-499的随机关注数
                0,
                NOW(), -- 当前时间作为创建时间
                NOW(), -- 当前时间作为更新时间
                FLOOR(RAND() * 2) -- 0或1的用户类型
            );
            
            SET current_id = current_id + 1;
            SET inserted_count = inserted_count + 1;
        END WHILE;
        
        -- 将临时表数据批量插入到user表
        INSERT INTO user SELECT * FROM temp_users;
        
        -- 输出当前进度
        SELECT CONCAT('已插入 ', inserted_count, ' 条记录，当前ID: ', current_id - 1) AS progress;
        
        SET inserted_count = 0;
        
        -- 释放临时表
        DROP TEMPORARY TABLE IF EXISTS temp_users;
    END WHILE;
    
    -- 记录结束时间并计算耗时
    SET @end_time = NOW();
    SELECT CONCAT('数据插入完成，耗时: ', TIMESTAMPDIFF(SECOND, @start_time, @end_time), ' 秒') AS summary;
END //

DELIMITER ;

-- 执行存储过程，插入ID从1到100000的记录
CALL insert_users_batch(1, 100000);

-- 方法2：如果存储过程执行失败，可使用以下简单SQL语句（适用于少量数据测试）
/*
-- 插入单条测试记录
INSERT INTO user (
    id, nickname, username, password, account_type, 
    country_code, phone, mail, avatar, verified, 
    signature, member_type, is_oauth, follower_count, 
    follow_count, status, created_at, updated_at, type
) VALUES (
    1, 
    '测试用户1',
    'testuser1',
    NULL,
    1,
    86,
    '13800000001',
    'test1@example.com',
    'https://example.com/avatar/1.jpg',
    0,
    '这是测试用户1的个性签名',
    0,
    0,
    0,
    0,
    0,
    NOW(),
    NOW(),
    0
);
*/

-- 验证插入结果
SELECT 
    COUNT(*) AS total_users,
    MIN(id) AS min_id,
    MAX(id) AS max_id,
    (SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user') AS current_auto_increment
FROM user;

-- 提示信息
SELECT '数据冷启动完成！后续插入的记录将从100001开始自增' AS completion_message;