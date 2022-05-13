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

if "__main__":
    db=connectDb("test")
    if db is None:
        exit(0)
    cursor = db.cursor()

    # role = 0 if user
    sql = """
    CREATE TABLE user (
        UID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        role boolean,
        account text(20),
        password varchar(64),
        name varchar(20),
        phone varchar(10),
        location geometry NOT NULL
    );
    """
    cursor.execute(sql)


    sql="""
    CREATE TABLE store (
        SID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        UID int,
        name varchar(20),
        category varchar(20),
        location geometry NOT NULL,
        FOREIGN KEY (UID) REFERENCES user(UID) 
    );
    """
    cursor.execute(sql)


    sql="""
    CREATE TABLE product (
        PID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        SID int,
        name varchar(20),
        picture varchar(50),
        price decimal,
        quantity int,
        FOREIGN KEY (SID) REFERENCES store(SID) 
    );
    """
    cursor.execute(sql)


    db.commit()

    # end connection
    db.close()
