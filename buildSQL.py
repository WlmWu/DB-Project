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

def createTable(db,tables):
    cursor = db.cursor()

    # role = 0 if user
    sql = """
    CREATE TABLE user (
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
    if tables['user']:
        cursor.execute(sql)


    sql="""
    CREATE TABLE store (
        SID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        UID int,
        name varchar(30) COLLATE utf8mb4_bin,
        category varchar(20),
        location geometry NOT NULL,
        FOREIGN KEY (UID) REFERENCES user(UID) 
    );
    """
    if tables['store']:
        cursor.execute(sql)


    sql="""
    CREATE TABLE product (
        PID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        SID int,
        name varchar(20) COLLATE utf8mb4_bin,
        picture longtext,
        price decimal,
        quantity int,
        imgType varchar(20),
        FOREIGN KEY (SID) REFERENCES store(SID) 
    );
    """
    if tables['product']:
        cursor.execute(sql)


    sql="""
    CREATE TABLE orders (
        OID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        UID int,
        SID int,
        status varchar(20),
        category varchar(20),
        start varchar(20),
        end varchar(20),
        amount decimal,
        distance decimal,
        FOREIGN KEY (UID) REFERENCES user(UID), 
        FOREIGN KEY (SID) REFERENCES store(SID) 
    );
    """
    if tables['orders']:
        cursor.execute(sql)


    sql="""
    CREATE TABLE content (
        OID int,
        PID int,
        amount int,
        FOREIGN KEY (OID) REFERENCES orders(OID), 
        FOREIGN KEY (PID) REFERENCES product(PID) 
    );
    """
    if tables['content']:
        cursor.execute(sql)


    sql="""
    CREATE TABLE transaction (
        TID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        UID int,
        action varchar(20),
        amount int,
        time varchar(20),
        FOREIGN KEY (UID) REFERENCES user(UID) 
    );
    """
    if tables['transaction']:
        cursor.execute(sql)

    db.commit()
    

if "__main__":
    db=connectDb("test")
    if db is None:
        exit(0)

    create_tables={'user':1,'store':1,'product':1,'orders':1,'content':1,'transaction':1}     # 1 to create, else: 0
    createTable(db,create_tables)

    # end connection
    db.close()