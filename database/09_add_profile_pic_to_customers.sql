-- Migration to add profile_pic column to customers table
ALTER TABLE customers
ADD COLUMN profile_pic VARCHAR(255) DEFAULT NULL;
