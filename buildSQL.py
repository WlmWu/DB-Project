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

    sql = """
    CREATE TABLE user (
        ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
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
        ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name varchar(20),
        category varchar(20),
        location geometry NOT NULL
    );
    """
    cursor.execute(sql)


    # sql="""
    # CREATE TABLE product (
    #     ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        
    # );
    # """
    # cursor.execute(sql)


    db.commit()

    # end connection
    db.close()
