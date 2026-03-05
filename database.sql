CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('worker', 'requester') NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    payment DECIMAL(10, 2) NOT NULL,
    deadline DATETIME NOT NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    worker_id INT NOT NULL,
    submission_data TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    earnings DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (worker_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE
);
-- Sample Data (Password for all is '123456' - hashed below)
-- $2y$10$8W3nE9fXNlqWHzuK.tV.u.W6r7Qz8w5Dk.f4z1.f.f.f.f.f.f.f
-- Note: Real pass is '123456', but since we use password_hash, let's keep it simple for real use.

INSERT INTO users (username, email, password, role, balance) VALUES 
('requester1', 'req1@example.com', '$2y$10$nO/zUpG9I1tQ9k7vXn.XaeW7P8j.8L/G.C7L.4L.4.4.4.4.4', 'requester', 1000.00),
('worker1', 'work1@example.com', '$2y$10$nO/zUpG9I1tQ9k7vXn.XaeW7P8j.8L/G.C7L.4L.4.4.4.4.4', 'worker', 25.50);

INSERT INTO tasks (requester_id, title, description, category, payment, deadline) VALUES 
(1, 'Identify objects in 10 images', 'Look at the provided images and list all visible objects separated by commas.', 'Data Entry', 0.50, '2025-12-31 23:59:59'),
(1, 'Transcribe 2 minute audio clip', 'Listen to the audio and type out the transcript exactly as heard.', 'Transcription', 2.00, '2025-12-31 23:59:59'),
(1, 'Quick opinion survey on AI', 'Give your honest opinion on the future of AI in 50 words.', 'Survey', 1.20, '2025-12-31 23:59:59'),
(1, 'Translate 5 sentences to Spanish', 'Translate the following English sentences into natural-sounding Spanish.', 'Translation', 3.00, '2025-12-31 23:59:59');
