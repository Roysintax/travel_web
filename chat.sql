-- =====================================================
-- DATABASE: CHAT SYSTEM - Tanah Suci Travel
-- File: chat.sql
-- Description: Database untuk fitur group chat
-- =====================================================
-- CATATAN: Import file ini ke database InfinityFree
-- Nama database: if0_40794045_tanah_suci_db
-- =====================================================

-- Tabel untuk menyimpan user chat (berdasarkan nama)
CREATE TABLE IF NOT EXISTS chat_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE COMMENT 'Nama user (harus unik)',
    color VARCHAR(20) DEFAULT '#D4AF37' COMMENT 'Warna untuk chat bubble',
    is_online TINYINT(1) DEFAULT 0 COMMENT 'Status online',
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='User chat';

-- Tabel untuk menyimpan pesan chat
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'ID user pengirim',
    message TEXT NOT NULL COMMENT 'Isi pesan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES chat_users(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Pesan chat';

-- Index untuk optimasi query
CREATE INDEX idx_chat_messages_created ON chat_messages(created_at);
CREATE INDEX idx_chat_users_online ON chat_users(is_online);
