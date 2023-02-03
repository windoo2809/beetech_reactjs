set global max_allowed_packet = 10000000;
ALTER USER api IDENTIFIED BY 'gS73DBfJ';
GRANT ALL PRIVILEGES ON landmark.* TO 'api'@'%';
FLUSH PRIVILEGES;

