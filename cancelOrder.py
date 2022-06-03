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

def popWindow(msg,is_succ=1):
    addr='nav.php' if is_succ else 'nav.php'
    print("<script>")
    print('window.location.href="%s";'%addr)
    print('window.alert("%s")'%msg)
    print("</script>")

form = cgi.FieldStorage() 
oid = form.getvalue('OID')
uName = form.getvalue('uName')
tm = form.getvalue('myOdrTime')


db=connectDb('test') 
if db is None:
    print('error')
    exit(0)

cursor=db.cursor()

sql="""
    SELECT status,UID,SID,amount
    FROM orders
    WHERE OID=%s
    """%(oid)
cursor.execute(sql)
sts,uid,sid,odrAmnt = cursor.fetchone()


# sts=1
msg=''
if sts==0:
    # change order status
    sql="""
        UPDATE orders
        SET status=-1
        WHERE OID=%s
        """%(oid)
    cursor.execute(sql)


    # $: shop -> user
    # shop->$
    sql="""
        SELECT UID,name
        FROM store
        WHERE SID=%s
        """%(sid)

    cursor.execute(sql)

    mngrID,sName = cursor.fetchone()
    mngrID=int(mngrID)

    sql="""
        INSERT INTO transaction
        (UID, action, amount, time, trader)
        VALUES(%s,2,%s,%s,%s)
        """
    cursor.execute(sql,[mngrID,-odrAmnt,tm,uName])


    # $->user
    sql="""
        SELECT wallet
        FROM user
        WHERE UID='%s'
        """%(uid)

    cursor.execute(sql)
    waltAmnt = int((cursor.fetchone())[0])

    sql="""
        UPDATE user
        SET wallet=%s
        WHERE UID=%s
        """%(odrAmnt+waltAmnt, uid)
    cursor.execute(sql)

    sql="""
        INSERT INTO transaction
        (UID, action, amount, time, trader)
        VALUES(%s,2,%s,%s,%s)
        """
    cursor.execute(sql,[uid,odrAmnt,tm,sName])


    # return inventories
    sql="""
        SELECT PID,amount
        FROM content
        WHERE OID=%s
        """%(oid)
    cursor.execute(sql)
    rlt = cursor.fetchall()
    for row in rlt:
        pid=row[0]
        quan=row[1]
        
        sql="""
            SELECT quantity
            FROM product
            WHERE PID=%s
            """%(pid)
        cursor.execute(sql)
        oriQuan=(cursor.fetchone())
        # check if product exists
        if oriQuan==None:
            continue

        oriQuan=int(oriQuan[0])
        sql="""
            UPDATE product
            SET quantity=%s
            WHERE PID=%s
            """%(oriQuan+quan, pid)
        cursor.execute(sql)


    msg='Canceled'
else:
    msg='The order has been finished!'

popWindow(msg)

db.commit()
db.close()