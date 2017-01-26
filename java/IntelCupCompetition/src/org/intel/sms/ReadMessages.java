// ReadMessages.java - Sample application.
//
// This application shows you the basic procedure needed for reading
// SMS messages from your GSM modem, in synchronous mode.
//

package org.intel.sms;

import org.intel.sqlite.crud.MessageCRUD;
import org.intel.sqlite.model.Message;
import org.intel.sqlite.util.Digest;
import org.smslib.CIncomingMessage;
import org.smslib.CService;
import org.smslib.ISmsMessageListener;

class ReadMessages extends Thread
{	
	CService srv;
	static MessageCRUD crud;
	static String date ;
	static String text ;

	// This is the message callback class.
	// The "received" method of this class is called by SMSLib API for each
	// message received.
	private static class CMessageListener implements ISmsMessageListener
	{
		public boolean received(CService service, CIncomingMessage message)
		{
			// Display the message received...
			System.out.println("Msg: " + message.toString());
		
			if(message.getDate() != null)
				date = message.getDate().toString();
			text = message.getText();

			/**
			 * Add database handler here
			 */
			
			// Initialize
			Message m = new Message();
			m.setDate(date);
			m.setEncoding(message.getMessageEncoding());
			m.setMemIndex(message.getMemIndex());
			m.setMemLocation(message.getMemLocation());
			m.setOrginator(message.getOriginator());
			m.setRefNo(message.getRefNo());
			m.setType(message.getType());
			m.setText(message.getText());
			
			m.set_id(Digest.md5(date+text));
			System.out.println("id:"+m.getId());
			crud.add(m);
			
			// and send a "thank you!" reply!
			try
			{
				// service.sendMessage(new
				// COutgoingMessage(message.getOriginator(), "Thank you!"));
			}
			catch (Exception e)
			{
				System.out.println("Could not send reply message!");
				e.printStackTrace();
			}

			// Return false to leave the message in memory - otherwise return
			// true to delete it.
			return false;
		}
	}
	public ReadMessages(){
		crud = new MessageCRUD();
	}
	
	
	@Override
	public void run() {
		srv = new CService("COM2", 57600, "GSM", "");

		// This is the listener callback class. This class will be called for
		// each message received.
		CMessageListener smsMessageListener = new CMessageListener();

		try
		{
			srv.setSimPin("0000");
			srv.setSmscNumber("");
			srv.connect();

			// Set the callback class.
			srv.setMessageHandler(smsMessageListener);
			// Set the polling interval in seconds.
			srv.setAsyncPollInterval(20);
			// Set the class of the messages to be read.
			srv.setAsyncRecvClass(CIncomingMessage.MessageClass.Unread);
			// Switch to asynchronous POLL mode.
			srv.setReceiveMode(CService.ReceiveMode.AsyncPoll);

			System.out.println("I will wait for a period of 60 secs for incoming messages...");
			try
			{
				Thread.sleep(60000);
			}
			catch (Exception e)
			{
			}
			System.out.println("Timeout period expired, exiting...");

			// Disconnect - Don't forget to disconnect!
			srv.disconnect();
		}
		catch (Exception e)
		{
			e.printStackTrace();
		}
	

	}
	
	
	/**
	 * 在程序�?出时释放
	 */
	@Override
	protected void finalize() throws Throwable {
		super.finalize();
		disconnect();
	}

	// �?��程序则调用这个函�?
	public void disconnect(){
		try {
			srv.disconnect();
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
	
	public static void main(String[] args)
	{	
		new ReadMessages().start();
	}
}
