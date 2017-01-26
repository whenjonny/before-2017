package org.intel.sqlite.model;

import java.io.Serializable;

public class Message implements Serializable{

	/**
	  	<property name="type" column="type" type="string"></property>
		<property name="encoding" column="encoding" type="string"></property>
		<property name="date" column="date" type="string"></property>
		<property name="orginator" column="orginator" type="string"></property>
		<property name="text" column="text" type="string"></property>
		<property name="refNo" column="refNo" type="int"></property>
		<property name="memIndex" column="memIndex" type="int"></property>
		<property name="memLocation" column="memLocation" type="string"></property>
	 */
	private static final long serialVersionUID = 1L;
	
	private int id;
	private String _id;
	
	private int type;	
	private int encoding;
	private String date;	
	private String orginator;
	private String text;	
	private int refNo;
	private int memIndex;
	private String memLocation;
	
	/**
	 * Id is unique
	 * @return
	 */
	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public int getType() {
		return type;
	}

	public void setType(int i) {
		this.type = i;
	}

	public int getEncoding() {
		return encoding;
	}

	public void setEncoding(int encoding) {
		this.encoding = encoding;
	}

	public String getDate() {
		return date;
	}

	public void setDate(String date) {
		this.date = date;
	}

	public String getText() {
		return text;
	}

	public void setText(String text) {
		this.text = text;
	}

	public int getRefNo() {
		return refNo;
	}

	public void setRefNo(int refNo) {
		this.refNo = refNo;
	}

	public int getMemIndex() {
		return memIndex;
	}

	public void setMemIndex(int memIndex) {
		this.memIndex = memIndex;
	}

	public String getMemLocation() {
		return memLocation;
	}

	public void setMemLocation(String memLocation) {
		this.memLocation = memLocation;
	}

	public String getOrginator() {
		return orginator;
	}

	public void setOrginator(String orginator) {
		this.orginator = orginator;
	}

	public String get_id() {
		return _id;
	}

	public void set_id(String _id) {
		this._id = _id;
	}

}
