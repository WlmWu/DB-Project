#!/Users/w/opt/anaconda3/bin/python3

print("Content-type:text/html")
print()

import cgi 
import re
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

form = cgi.FieldStorage() 
uid = form.getvalue('uid')
name = form.getvalue('name')
tm = form.getvalue('time')
val = form.getvalue('value')


db=connectDb('test') 
if db is None:
    print('error')
    exit(0)

cursor=db.cursor()

sql="""
    select wallet
    from user
    where UID='%s'
    """%(uid)

cursor.execute(sql)
oriVal = (cursor.fetchone())[0]

sql="""
    UPDATE user
    SET wallet=%s
    WHERE UID=%s
    """%(int(val)+oriVal, uid)
cursor.execute(sql)

sql="""
    INSERT INTO transaction
    (UID, action, amount, time, trader)
    VALUES(%s,0,%s,'%s','%s')
    """%(uid,val,tm,name)
cursor.execute(sql)

db.commit()

db.close()