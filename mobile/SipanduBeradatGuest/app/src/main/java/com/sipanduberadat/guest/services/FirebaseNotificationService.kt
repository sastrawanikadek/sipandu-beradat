package com.sipanduberadat.guest.services

import android.app.Notification
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.content.SharedPreferences
import android.graphics.Bitmap
import android.graphics.drawable.Drawable
import android.media.AudioAttributes
import android.media.RingtoneManager
import android.net.Uri
import android.os.Build
import android.os.PowerManager
import android.util.Log
import android.view.View
import androidx.core.app.NotificationCompat
import androidx.core.content.ContextCompat
import com.bumptech.glide.Glide
import com.bumptech.glide.request.target.CustomTarget
import com.bumptech.glide.request.transition.Transition
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.activities.MainActivity
import com.sipanduberadat.guest.services.apis.addLocationHistoryAPI
import com.sipanduberadat.guest.utils.requestLocation
import org.json.JSONObject


class FirebaseNotificationService: FirebaseMessagingService() {

    @Suppress("DEPRECATION")
    override fun onMessageReceived(p0: RemoteMessage) {
        super.onMessageReceived(p0)
        val data = p0.data
        val notificationManager: NotificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as
                NotificationManager
        val sharedPreferences = getSharedPreferences("PREFERENCES", Context.MODE_PRIVATE)

        when (data["notification_type"]) {
            "request-location" -> {
                requestLocation(this) {
                    val requestParams = HashMap<String, String>()
                    requestParams["latitude"] = "${it.latitude}"
                    requestParams["longitude"] = "${it.longitude}"

                    addLocationHistoryAPI(View(this), this, requestParams, HashMap(),
                            {}, {}, showMessage = false)
                }
            }
            "family-request" -> {
                val editor: SharedPreferences.Editor = sharedPreferences.edit()
                val soundUri: Uri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION)
                val intent = Intent(this, MainActivity::class.java)
                intent.putExtra("NOTIFICATION_TYPE", "family-request")
                intent.putExtra("NOTIFICATION_ACTION_ID",
                        JSONObject(data["notification_data"]!!).getString("id"))
                val pendingIntent = PendingIntent.getActivity(this, 0, intent,
                        PendingIntent.FLAG_UPDATE_CURRENT)
                editor.putInt("NOTIFICATION_COUNT", sharedPreferences.getInt("NOTIFICATION_COUNT", 0) + 1)
                editor.apply()

                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                    val notificationChannel = NotificationChannel("NOTIFICATION", "NOTIFICATION",
                            NotificationManager.IMPORTANCE_HIGH)

                    val att = AudioAttributes.Builder()
                            .setUsage(AudioAttributes.USAGE_NOTIFICATION)
                            .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                            .build()
                    notificationChannel.setSound(soundUri, att)
                    notificationChannel.lockscreenVisibility = Notification.VISIBILITY_PUBLIC

                    notificationManager.createNotificationChannel(notificationChannel)
                }

                Glide.with(this).asBitmap().load(data["notification_photo"])
                        .into(object: CustomTarget<Bitmap>() {
                            override fun onResourceReady(resource: Bitmap, transition: Transition<in Bitmap>?) {
                                val notification = NotificationCompat.Builder(this@FirebaseNotificationService,
                                        "NOTIFICATION").apply {
                                    setSmallIcon(R.drawable.ic_logo)
                                    setLargeIcon(resource)
                                    setContentTitle(data["notification_title"])
                                    setContentText(data["notification_message"])
                                    setStyle(NotificationCompat.BigTextStyle().bigText(data["notification_message"]))
                                    setSound(soundUri)
                                    setContentIntent(pendingIntent)
                                    setCategory(NotificationCompat.CATEGORY_SOCIAL)
                                    setAutoCancel(true)
                                    setVisibility(NotificationCompat.VISIBILITY_PUBLIC)
                                    color = ContextCompat.getColor(this@FirebaseNotificationService,
                                            R.color.red_700)
                                    priority = NotificationCompat.PRIORITY_HIGH
                                }.build()
                                notificationManager.notify(0, notification)

                                val powerManager: PowerManager = getSystemService(Context.POWER_SERVICE) as PowerManager

                                if (!powerManager.isInteractive) {
                                    powerManager.newWakeLock(PowerManager.FULL_WAKE_LOCK
                                            or PowerManager.ACQUIRE_CAUSES_WAKEUP or PowerManager.ON_AFTER_RELEASE,
                                            "SiPanduBeradat:notificationWakeLock")
                                            .acquire(5000)
                                }
                            }

                            override fun onLoadCleared(placeholder: Drawable?) {}
                        })
            }
            "report" -> {
                val withPhoto = data["notification_photo"] != null
                val soundUri: Uri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION)
                val intent = Intent(this, MainActivity::class.java)
                intent.putExtra("NOTIFICATION_TYPE", "report")
                intent.putExtra("NOTIFICATION_ACTION_ID",
                        JSONObject(data["notification_data"]!!).getLong("id"))
                val pendingIntent = PendingIntent.getActivity(this, 0, intent,
                        PendingIntent.FLAG_UPDATE_CURRENT)

                if (withPhoto) {
                    val editor: SharedPreferences.Editor = sharedPreferences.edit()
                    editor.putInt("NOTIFICATION_COUNT", sharedPreferences.getInt("NOTIFICATION_COUNT", 0) + 1)
                    editor.apply()
                }

                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                    val notificationChannel = NotificationChannel("NOTIFICATION", "NOTIFICATION",
                            NotificationManager.IMPORTANCE_HIGH)

                    val att = AudioAttributes.Builder()
                            .setUsage(AudioAttributes.USAGE_NOTIFICATION)
                            .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                            .build()
                    notificationChannel.setSound(soundUri, att)
                    notificationChannel.lockscreenVisibility = Notification.VISIBILITY_PUBLIC

                    notificationManager.createNotificationChannel(notificationChannel)
                }

                Glide.with(this).asBitmap().load(if (withPhoto) data["notification_photo"] else R.drawable.logo)
                        .into(object: CustomTarget<Bitmap>() {
                            override fun onResourceReady(resource: Bitmap, transition: Transition<in Bitmap>?) {
                                val notification = NotificationCompat.Builder(this@FirebaseNotificationService,
                                        "NOTIFICATION").apply {
                                    setSmallIcon(R.drawable.ic_logo)
                                    if (withPhoto) setLargeIcon(resource)
                                    setContentTitle(data["notification_title"])
                                    setContentText(data["notification_message"])
                                    setStyle(NotificationCompat.BigTextStyle().bigText(data["notification_message"]))
                                    setSound(soundUri)
                                    setContentIntent(pendingIntent)
                                    setCategory(NotificationCompat.CATEGORY_SOCIAL)
                                    setAutoCancel(true)
                                    setVisibility(NotificationCompat.VISIBILITY_PUBLIC)
                                    color = ContextCompat.getColor(this@FirebaseNotificationService,
                                            R.color.red_700)
                                    priority = NotificationCompat.PRIORITY_HIGH
                                }.build()
                                notificationManager.notify(0, notification)

                                val powerManager: PowerManager = getSystemService(Context.POWER_SERVICE) as PowerManager

                                if (!powerManager.isInteractive) {
                                    powerManager.newWakeLock(PowerManager.FULL_WAKE_LOCK
                                            or PowerManager.ACQUIRE_CAUSES_WAKEUP or PowerManager.ON_AFTER_RELEASE,
                                            "SiPanduBeradat:notificationWakeLock")
                                            .acquire(5000)
                                }
                            }

                            override fun onLoadCleared(placeholder: Drawable?) {}
                        })
            }
            "emergency-report" -> {
                val withPhoto = data["notification_photo"] != null
                val soundUri: Uri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION)
                val intent = Intent(this, MainActivity::class.java)
                intent.putExtra("NOTIFICATION_TYPE", "emergency-report")
                intent.putExtra("NOTIFICATION_ACTION_ID",
                        JSONObject(data["notification_data"]!!).getLong("id"))
                val pendingIntent = PendingIntent.getActivity(this, 0, intent,
                        PendingIntent.FLAG_UPDATE_CURRENT)

                if (withPhoto) {
                    val editor: SharedPreferences.Editor = sharedPreferences.edit()
                    editor.putInt("NOTIFICATION_COUNT", sharedPreferences.getInt("NOTIFICATION_COUNT", 0) + 1)
                    editor.apply()
                }

                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                    val notificationChannel = NotificationChannel("NOTIFICATION", "NOTIFICATION",
                            NotificationManager.IMPORTANCE_HIGH)

                    val att = AudioAttributes.Builder()
                            .setUsage(AudioAttributes.USAGE_NOTIFICATION)
                            .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                            .build()
                    notificationChannel.setSound(soundUri, att)
                    notificationChannel.lockscreenVisibility = Notification.VISIBILITY_PUBLIC

                    notificationManager.createNotificationChannel(notificationChannel)
                }

                Glide.with(this).asBitmap().load(if (withPhoto) data["notification_photo"] else R.drawable.logo)
                        .into(object: CustomTarget<Bitmap>() {
                            override fun onResourceReady(resource: Bitmap, transition: Transition<in Bitmap>?) {
                                val notification = NotificationCompat.Builder(this@FirebaseNotificationService,
                                        "NOTIFICATION").apply {
                                    setSmallIcon(R.drawable.ic_logo)
                                    if (withPhoto) setLargeIcon(resource)
                                    setContentTitle(data["notification_title"])
                                    setContentText(data["notification_message"])
                                    setStyle(NotificationCompat.BigTextStyle().bigText(data["notification_message"]))
                                    setSound(soundUri)
                                    setContentIntent(pendingIntent)
                                    setCategory(NotificationCompat.CATEGORY_SOCIAL)
                                    setAutoCancel(true)
                                    setVisibility(NotificationCompat.VISIBILITY_PUBLIC)
                                    color = ContextCompat.getColor(this@FirebaseNotificationService,
                                            R.color.red_700)
                                    priority = NotificationCompat.PRIORITY_HIGH
                                }.build()
                                notificationManager.notify(0, notification)

                                val powerManager: PowerManager = getSystemService(Context.POWER_SERVICE) as PowerManager

                                if (!powerManager.isInteractive) {
                                    powerManager.newWakeLock(PowerManager.FULL_WAKE_LOCK
                                            or PowerManager.ACQUIRE_CAUSES_WAKEUP or PowerManager.ON_AFTER_RELEASE,
                                            "SiPanduBeradat:notificationWakeLock")
                                            .acquire(5000)
                                }
                            }

                            override fun onLoadCleared(placeholder: Drawable?) {}
                        })
            }
        }
    }

    override fun onNewToken(p0: String) {
        super.onNewToken(p0)

        Log.e("TOKEN", p0)
    }

}