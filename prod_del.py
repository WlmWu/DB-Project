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

def popWindow(msg,is_succ):
    addr='nav.php'
    print("<script>")
    print('window.location.href="%s";'%addr)
    print('window.alert("%s")'%msg)
    print("</script>")

form = cgi.FieldStorage() 
PID = form.getvalue('PID')

db=connectDb('test') 
if db is None:
    print('error')
    exit(0)
cursor=db.cursor()
sql="""
    DELETE FROM product
    WHERE product.PID=%s
    """%(PID)
cursor.execute(sql)
db.commit()

popWindow('Delete Success.',1)

db.close()
