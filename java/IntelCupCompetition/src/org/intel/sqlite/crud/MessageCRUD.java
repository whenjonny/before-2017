package org.intel.sqlite.crud;

import org.hibernate.Session;
import org.hibernate.Transaction;
import org.intel.sqlite.model.Message;
import org.intel.sqlite.util.Digest;
import org.intel.sqlite.util.HibernateUtil;

public class MessageCRUD {
	Session session;
	/**
	 * Implement Two CRUD for add and delete
	 */
	public MessageCRUD(){
		session = HibernateUtil.getSessionFactory().openSession(); 
	}
	
	public boolean add(Message message){
		Transaction tx = session.beginTransaction();
		session.save(message);		
		tx.commit();
		return true;
	}
	
	public void delete(String id){
		Transaction tx = session.beginTransaction();
		String hql = "DELETE from Message where id = ?";
		session.createQuery(hql)
			.setString(0, id)
			.executeUpdate();
		tx.commit();
	}
	
	public void shutdown(){
		session.close();
		HibernateUtil.shutdown();
	}
	
	public static void main(String[] arg){
		Message message = new Message();
		//message.setId(Digest.md5("hello"));
		MessageCRUD m = new MessageCRUD();
		//m.add(message);
		m.delete("b74a441698c7b1a7ebc8fee2da9ce974");
		m.shutdown();
	}

}
