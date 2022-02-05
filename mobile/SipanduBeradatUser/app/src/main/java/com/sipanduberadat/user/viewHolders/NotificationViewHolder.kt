package com.sipanduberadat.user.viewHolders

import android.content.Intent
import android.view.View
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.sipanduberadat.user.activities.ProfileKramaActivity
import com.sipanduberadat.user.activities.ReportDetailActivity
import com.sipanduberadat.user.models.Me
import com.sipanduberadat.user.models.Notifikasi
import kotlinx.android.synthetic.main.layout_item_notification.view.*

class NotificationViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    fun onBindItem(notification: Notifikasi, me: Me) {
        Glide.with(view.context).load(notification.photo).centerCrop().into(view.photo)
        view.title_text.text = notification.title
        view.description.text = notification.description
        view.container.setOnClickListener {
            when (notification.type) {
                0 -> {
                    val intent = Intent(view.context, ProfileKramaActivity::class.java)
                    intent.putExtra("ME", me)
                    intent.putExtra("KRAMA_ID", "${notification.data}")
                    view.context.startActivity(intent)
                }
                1 -> {
                    val intent = Intent(view.context, ReportDetailActivity::class.java)
                    intent.putExtra("ME", me)
                    intent.putExtra("REPORT_ID", notification.data)
                    intent.putExtra("REPORT_EMERGENCY_STATUS", false)
                    intent.putExtra("IS_REPORTER_KRAMA", true)
                    view.context.startActivity(intent)
                }
                2 -> {
                    val intent = Intent(view.context, ReportDetailActivity::class.java)
                    intent.putExtra("ME", me)
                    intent.putExtra("REPORT_ID", notification.data)
                    intent.putExtra("REPORT_EMERGENCY_STATUS", true)
                    intent.putExtra("IS_REPORTER_KRAMA", true)
                    view.context.startActivity(intent)
                }
            }
        }
    }
}