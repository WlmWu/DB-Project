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
shop_name = form.getvalue('shopname')

if shop_name!=None:
    db=connectDb('test') 
    if db is None:
        print('error')
        exit(0)
    cursor=db.cursor()
    sql="""
        select *
        from store
        where name=%(shop_name)s
        """
    cursor.execute(sql, {'shop_name':shop_name})
    rlt = cursor.fetchone()

    if rlt!=None:
        msg='The shop name has been registered.'

    alertMsg(msg)