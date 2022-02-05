package com.sipanduberadat.user.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.sipanduberadat.user.R
import com.sipanduberadat.user.models.Me
import com.sipanduberadat.user.models.Notifikasi
import com.sipanduberadat.user.viewHolders.NotificationViewHolder

class NotificationAdapter(
    private val context: Context,
    private val items: List<Notifikasi>,
    private val me: Me
): RecyclerView.Adapter<NotificationViewHolder>() {
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): NotificationViewHolder {
        val view = LayoutInflater.from(context).inflate(R.layout.layout_item_notification, parent,
            false)
        return NotificationViewHolder(view)
    }

    override fun getItemCount(): Int {
        return items.size
    }

    override fun onBindViewHolder(holder: NotificationViewHolder, position: Int) {
        holder.onBindItem(items[position], me)
    }
}