package org.intel.jnitts;
public class smartreadjava {

	// ��ʼ����֤����,ʹ����������ǰ�������ȵ���,������֤��
	public native int SmartRead_InitialAuth(long hwndFrom, long hwndMessage,
			long hwndprocess, long hwndToShow, String chMailBox,
			String chPassword, String chValidateCode);

	// �رպ������Ƴ�����ǰ�������
	public native int SmartRead_Close();

	// �ʶ�����
	public native int SmartRead_Speak(String lpStr, String lpStrShow,
			int iStyle, int iSpeech, int iRate, int iVolume, int iPunctuation,
			int iSelVoiceDevice, String lpLink);

	// ֹͣ�ʶ�����
	public native int SmartRead_Stop();

	// ���������öԻ������ڸı���Ӣ�����������������٣����Զ�����������
	public native int SmartRead_SetDialog(long hwndFather, String lpDownPage);

	// ���ı��ʶ���WAVE�ļ��ĺ�����������������ļ����ʶ��������ʶ����٣��ʶ�����
	public native int SmartRead_SpeakToWave(String lpStr, String lpStrShow,
			String lpWaveFile, int iStyle, int iSpeech, int iRate, int iVolume,
			int iFormat, int iPunctuation, String lpLink);

	// ����ʶ���λ��
	public native int SmartRead_GetLocationInfo();

	// ���ʶ�������,�ı�����,��Χ��0-100֮��,100Ϊ�����.
	public native int SmartRead_SetVolume(int iVolume);

	// ���ʶ�������,�������,��Χ��0-100֮��,100Ϊ�����.
	public native int SmartRead_GetVolume();

	// ���ʶ�������,�ı�����,��Χ��0-100ֱ��,100Ϊ����ٶ�.
	public native int SmartRead_SetSpeed(int iSpeed);

	// ���ʶ�������,�������,��Χ��0-100ֱ��,100Ϊ����ٶ�.
	public native int SmartRead_GetSpeed();

	// ���ʶ�������,ʵ����ͣ/��������.
	public native int SmartRead_PauseORContinue();

	// ����ֵΪ��̨��������������Ŀ
	public native int SmartRead_GetVoiceDeviceNum();

	// ��ô������
	public native int SmartRead_GetErrCode();

	// ��ô�����Ϣ
	public native String SmartRead_GetErrMsg();

	// ���ò���
	public native int SmartRead_SetParameter(int iParameterName,
			int iParameterNum);

	// ����ģʽ
	public native int SmartRead_SetMode(int iModeStyle);

	// ���в�����һ��
	public native int SmartRead_QueueNext();

	// ��ö��в�����Ϣ
	public native int SmartRead_QueueStatistics(int iReturnSelect);

	// ������ʾ��Ϣ
	public native int SmartRead_HideMessageBox();

	// �汾��Ϣ
	public native String SmartRead_Version();

	static {
		System.loadLibrary("smartread7");
	}

}