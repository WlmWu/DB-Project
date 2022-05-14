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

def alertMsg(msg):
    print("<span style='color:red'>"+msg+"</span>")

form = cgi.FieldStorage() 
acnt = form.getvalue('Account')

if acnt!=None:
    parser=re.compile("[ ]*([A-Za-z0-9]*)[ ]*")
    match=parser.fullmatch(acnt)
    msg=""
    if match:
        db=connectDb('test') 
        if db is None:
            print('error')
            exit(0)
        cursor=db.cursor()
        sql="""
            select account
            from user
            where account='%s'
            """%(acnt)
        cursor.execute(sql)
        rlt = cursor.fetchone()

        if rlt!=None:
            msg='Account has been registered.'
    else:
        msg='Account contains English alphabets and numbers only.'
    alertMsg(msg)