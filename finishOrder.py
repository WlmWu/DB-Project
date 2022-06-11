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
print(sts)
print(uid)
print(sid)
print(odrAmnt)

# sts=1
valid=0
msg=''

if sts==0:
    # change order status
    sql="""
        UPDATE orders
        SET status=1
        WHERE OID=%s
        """%(oid)
    cursor.execute(sql)

    # $: user -> shop
    # user->$
    
    # $->shop
    sql="""
        SELECT UID
        FROM store
        WHERE SID=%s
        """%(sid)

    cursor.execute(sql)
    mngrID = int((cursor.fetchone())[0])
    
    sql="""
        SELECT wallet
        FROM user
        WHERE UID=%s
        """%(mngrID)

    cursor.execute(sql)
    waltAmnt = int((cursor.fetchone())[0])
    
    sql="""
        UPDATE user
        SET wallet=%s
        WHERE UID=%s
        """%(odrAmnt+waltAmnt, mngrID)
    cursor.execute(sql)

    # add end time
    sql="""
        UPDATE orders
        SET end=%s
        WHERE OID=%s
        """
    cursor.execute(sql,[tm, oid])

    msg='Finished'
else:
    msg='The order has been Canceled!'

popWindow(msg)

db.commit()
db.close()