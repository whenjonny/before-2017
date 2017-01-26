package org.intel.jnitts;
public class smartreadjava {

	// 初始化认证函数,使用语音功能前必须首先调用,进行认证。
	public native int SmartRead_InitialAuth(long hwndFrom, long hwndMessage,
			long hwndprocess, long hwndToShow, String chMailBox,
			String chPassword, String chValidateCode);

	// 关闭函数，推出程序前必须调用
	public native int SmartRead_Close();

	// 朗读函数
	public native int SmartRead_Speak(String lpStr, String lpStrShow,
			int iStyle, int iSpeech, int iRate, int iVolume, int iPunctuation,
			int iSelVoiceDevice, String lpLink);

	// 停止朗读函数
	public native int SmartRead_Stop();

	// 打开语音设置对话框，用于改变中英文语音，音量，语速，可自定义下载连接
	public native int SmartRead_SetDialog(long hwndFather, String lpDownPage);

	// 将文本朗读到WAVE文件的函数，可设置输出的文件，朗读语音，朗读语速，朗读音量
	public native int SmartRead_SpeakToWave(String lpStr, String lpStrShow,
			String lpWaveFile, int iStyle, int iSpeech, int iRate, int iVolume,
			int iFormat, int iPunctuation, String lpLink);

	// 获得朗读的位置
	public native int SmartRead_GetLocationInfo();

	// 在朗读过程中,改变音量,范围在0-100之间,100为最高音.
	public native int SmartRead_SetVolume(int iVolume);

	// 在朗读过程中,获得音量,范围在0-100之间,100为最高音.
	public native int SmartRead_GetVolume();

	// 在朗读过程中,改变语速,范围在0-100直接,100为最快速度.
	public native int SmartRead_SetSpeed(int iSpeed);

	// 在朗读过程中,获得语速,范围在0-100直接,100为最快速度.
	public native int SmartRead_GetSpeed();

	// 在朗读过程中,实现暂停/继续功能.
	public native int SmartRead_PauseORContinue();

	// 返回值为本台电脑上声卡的数目
	public native int SmartRead_GetVoiceDeviceNum();

	// 获得错误代码
	public native int SmartRead_GetErrCode();

	// 获得错误信息
	public native String SmartRead_GetErrMsg();

	// 设置参数
	public native int SmartRead_SetParameter(int iParameterName,
			int iParameterNum);

	// 设置模式
	public native int SmartRead_SetMode(int iModeStyle);

	// 队列播放下一条
	public native int SmartRead_QueueNext();

	// 获得队列播放信息
	public native int SmartRead_QueueStatistics(int iReturnSelect);

	// 隐藏提示信息
	public native int SmartRead_HideMessageBox();

	// 版本信息
	public native String SmartRead_Version();

	static {
		System.loadLibrary("smartread7");
	}

}