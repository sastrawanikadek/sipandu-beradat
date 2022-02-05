package com.sipanduberadat.guest.viewHolders

import android.content.Intent
import android.view.View
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.sipanduberadat.guest.activities.ProfileKramaActivity
import com.sipanduberadat.guest.activities.ReportDetailActivity
import com.sipanduberadat.guest.models.Notifikasi
import com.sipanduberadat.guest.models.Tamu
import kotlinx.android.synthetic.main.layout_item_notification.view.*

class NotificationViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    fun onBindItem(notification: Notifikasi) {
        Glide.with(view.context).load(notification.photo).centerCrop().into(view.photo)
        view.title_text.text = notification.title
        view.description.text = notification.description
        view.container.setOnClickListener {
            when (notification.type) {
                0 -> {
                    val intent = Intent(view.context, ProfileKramaActivity::class.java)
                    intent.putExtra("GUEST_ID", "${notification.data}")
                    view.context.startActivity(intent)
                }
                1 -> {
                    val intent = Intent(view.context, ReportDetailActivity::class.java)
                    intent.putExtra("REPORT_ID", notification.data)
                    intent.putExtra("REPORT_EMERGENCY_STATUS", false)
                    intent.putExtra("IS_REPORTER_KRAMA", false)
                    view.context.startActivity(intent)
                }
                2 -> {
                    val intent = Intent(view.context, ReportDetailActivity::class.java)
                    intent.putExtra("REPORT_ID", notification.data)
                    intent.putExtra("REPORT_EMERGENCY_STATUS", true)
                    intent.putExtra("IS_REPORTER_KRAMA", false)
                    view.context.startActivity(intent)
                }
            }
        }
    }
}