package org.intel.sqlite.test;

import junit.framework.TestCase;

import org.hibernate.Session;
import org.hibernate.Transaction;
import org.intel.sqlite.model.Message;
import org.intel.sqlite.util.HibernateUtil;


public class TestSaveMessage extends TestCase{

	public void testSave(){
		Message user = new Message();
		user.setText("outcoming");
		Session session = HibernateUtil.getSessionFactory().openSession();
		Transaction tx = session.beginTransaction();
		session.save(user);
		tx.commit();
		session.close();
		HibernateUtil.shutdown();
	}
}
