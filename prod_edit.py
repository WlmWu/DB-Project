#!/Users/w/opt/anaconda3/bin/python3

print("Content-type:text/html")
print()

import cgi
import re
import pymysql
import logging
import cgitb; cgitb.enable()

print("<h1>enter prod_edit.py</h1>")

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
    addr='nav.php' if is_succ else 'nav.php'
    print("<script>")
    print('window.location.href="%s";'%addr)
    print('window.alert("%s")'%msg)
    print("</script>")

form = cgi.FieldStorage() 
PID = form.getvalue('PID')
n_pri = form.getvalue('price')
n_quan = form.getvalue('quantity')

valid=0

try:
    if n_pri==None:
        assert False, 'Price is empty.'
    if n_quan==None:
        assert False, 'Quantity is empty.'
except AssertionError as msg:
    popWindow(msg,0)

try:
    parser=re.compile("[ ]*([1-9][0-9]*)[ ]*")
    match=parser.fullmatch(n_pri)
    if match:
        name=match.group(0)
        if (int)(n_pri)<0:
            assert False, 'A price is a positive integer.'
    else:
        assert False, 'A price is a positive integer.'
    
    parser=re.compile("[ ]*([1-9][0-9]*)[ ]*")
    match=parser.fullmatch(n_quan)
    if match:
        acnt=match.group(0)
        if (int)(n_quan)<0:
            assert False, 'A quantity is a positive integer.'
    else:
        assert False, 'A quantity is a positive integer.'
    valid =1
except AssertionError as msg:
    popWindow(msg,0)

if valid:
    db=connectDb('test') 
    if db is None:
        print('error')
        exit(0)

    cursor=db.cursor()
    sql="""
        UPDATE product
        SET price=%s
        WHERE product.PID=%s
        """%(n_pri, PID)
    cursor.execute(sql)
    db.commit()

    sql="""
        UPDATE product
        SET quantity=%s
        WHERE product.PID=%s
        """%(n_quan, PID)
    cursor.execute(sql)
    db.commit()

    popWindow('Edit Success.',1)

    db.close()