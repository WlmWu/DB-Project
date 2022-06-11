#!/usr/bin/python3
#coding=utf-8

import pymysql
import logging

def connectDb(dbName):
    try:
        mysqldb = pymysql.connect(
                host="127.0.0.1",
                user="root",
                port=3306,
                database=dbName)
        return mysqldb
    except Exception as e:
        logging.error('Fail to connection mysql {}'.format(str(e)))
    return None

def createTable(db):
    cursor = db.cursor()

    # role = 0 if user
    sql = """
    CREATE TABLE IF NOT EXISTS user (
        UID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        account varchar(20) COLLATE utf8mb4_bin,
        password varchar(64),
        name varchar(20),
        phone varchar(10),
        location geometry NOT NULL,
        role boolean,
        wallet int
    );
    """

    cursor.execute(sql)


    sql="""
    CREATE TABLE IF NOT EXISTS store (
        SID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        UID int,
        name varchar(30) COLLATE utf8mb4_bin,
        category varchar(20),
        location geometry NOT NULL,
        FOREIGN KEY (UID) REFERENCES user(UID) 
    );
    """

    cursor.execute(sql)


    sql="""
    CREATE TABLE IF NOT EXISTS product (
        PID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        SID int,
        name varchar(30) COLLATE utf8mb4_bin,
        picture longtext,
        price decimal,
        quantity int,
        imgType varchar(20),
        FOREIGN KEY (SID) REFERENCES store(SID) 
    );
    """

    cursor.execute(sql)

    
    # status: 1: finish/ 0: ~finish/ -1: cancel
    # category: 0: take out/ 1: delivery
    sql="""
    CREATE TABLE IF NOT EXISTS orders (
        OID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        UID int,
        SID int,
        status int,
        category boolean,
        start varchar(20),
        end varchar(20),
        amount decimal,
        distance decimal(10,3),
        FOREIGN KEY (UID) REFERENCES user(UID), 
        FOREIGN KEY (SID) REFERENCES store(SID) 
    );
    """

    cursor.execute(sql)


    sql="""
    CREATE TABLE IF NOT EXISTS content (
        OID int,
        PID int,
        amount int,
        FOREIGN KEY (OID) REFERENCES orders(OID), 
        FOREIGN KEY (PID) REFERENCES product(PID) ON DELETE CASCADE
    );
    """

    cursor.execute(sql)

    # action: -1: payment/ 0: recharge/ 1: receive/ 2: refund
    sql="""
    CREATE TABLE IF NOT EXISTS transaction (
        TID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        UID int,
        action int,
        amount int,
        time varchar(20),
        trader varchar(30) COLLATE utf8mb4_bin,
        FOREIGN KEY (UID) REFERENCES user(UID) 
    );
    """

    cursor.execute(sql)

    db.commit()
    

if "__main__":
    db=connectDb("test")
    if db is None:
        exit(0)

    createTable(db)

    # end connection
    db.close()