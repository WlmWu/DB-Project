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

uid = form.getvalue('UID')
sid = form.getvalue('SID')
amnt = form.getvalue('amnt')
dis = form.getvalue('dis')
tm = form.getvalue('time')
ctgry = form.getvalue('ctgry')
walt = form.getvalue('walt')
sName = form.getvalue('sName')
name = form.getvalue('name')

pid = form.getlist('mPID')
quan = form.getlist('mQuan')

walt=int(walt)
amnt=int(amnt)
ctgry=1 if (ctgry=='1') else 0


db=connectDb('test') 
if db is None:
    print('error')
    exit(0)

cursor=db.cursor()

valid=0
msg=''
try:
    if walt<amnt:
        assert False, "Balance isn't enough."
    
    chkAmnt=0
    for i in range(len(pid)):
        sql="""
        SELECT quantity,price
        FROM product
        WHERE PID=%s
        """%(pid[i])
        cursor.execute(sql)

        rlt=cursor.fetchone()
        if rlt==None:
            assert False, "Some products don't exist. Please try again"
        
        inven = int(rlt[0])
        odrQuan=int(quan[i])
        if inven<odrQuan:
            assert False, "Inventory isn't enough."
        else:
            pPrice=int(rlt[1])
            chkAmnt+=pPrice*odrQuan
    deliFee=round(float(dis)*10) if round(float(dis)*10)!=0 else 10
    chkAmnt+=deliFee
    if chkAmnt!=amnt:
        assert False, "The price has changed. Please order again."
    
    valid=1
except AssertionError as msg:
    popWindow(msg)

if valid:
    # create an user order
    sql="""
    INSERT INTO orders
    (UID, SID, status, category, start, amount, distance)
    VALUES (%s, %s, 0, %s, '%s', %s, %s)
    """%(uid, sid, ctgry, tm, amnt, dis)
    cursor.execute(sql)

    sql="""
        SELECT OID
        FROM orders
        WHERE start='%s' and UID=%s and SID=%s
        """%(tm,uid,sid)
    cursor.execute(sql)
    oid = (cursor.fetchone())[0]

    for i in range(len(pid)):
        if(int(quan[i])>0):
            sql="""
                INSERT INTO content
                (OID, PID, amount)
                VALUES (%s, %s, %s)
                """%(oid,pid[i],quan[i])
            cursor.execute(sql)

    # pay up
    sql="""
        UPDATE user
        SET wallet=%s
        WHERE UID=%s
        """%(walt-amnt, uid)
    cursor.execute(sql)
    
    # create a user t_record
    sql="""
        INSERT INTO transaction
        (UID, action, amount, time, trader)
        VALUES(%s,-1,%s,%s,%s)
        """
    cursor.execute(sql,[uid,-amnt,tm,sName])

    # create a manager t_record
    sql="""
        SELECT UID
        FROM store
        WHERE SID=%s
        """%(sid)
    cursor.execute(sql)
    mngrID = (cursor.fetchone())[0]

    sql="""
        INSERT INTO transaction
        (UID, action, amount, time, trader)
        VALUES(%s,1,%s,%s,%s)
        """
    cursor.execute(sql,[mngrID,amnt,tm,name])

    # reduce inventories
    for i in range(len(pid)):
        if(int(quan[i])>0):
            sql="""
                select quantity
                from product
                where PID='%s'
                """%(pid[i])
            cursor.execute(sql)
            oriQuan = (cursor.fetchone())[0]

            sql="""
                UPDATE product
                SET quantity=%s
                WHERE PID=%s
                """%(oriQuan-int(quan[i]), pid[i])
            cursor.execute(sql)

    db.commit()
    db.close()

    popWindow('Order Success')




