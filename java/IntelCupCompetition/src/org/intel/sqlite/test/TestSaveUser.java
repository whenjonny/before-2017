package org.intel.sqlite.test;

import junit.framework.TestCase;

import org.hibernate.Session;
import org.hibernate.Transaction;
import org.intel.sqlite.model.User;
import org.intel.sqlite.util.HibernateUtil;


public class TestSaveUser extends TestCase{

	public void testSave(){
		User user = new User();
		user.setName("apple");
		user.setPassword("123456");
		Session session = HibernateUtil.getSessionFactory().openSession();
		Transaction tx = session.beginTransaction();
		session.save(user);
		tx.commit();
		session.close();
		HibernateUtil.shutdown();
	}
}
