package com.sipanduberadat.guest.viewHolders

import android.content.Intent
import android.view.View
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.sipanduberadat.guest.activities.ProfileKramaActivity
import com.sipanduberadat.guest.models.KerabatTamu
import kotlinx.android.synthetic.main.layout_request_family_item.view.*

class RequestFamilyViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    fun onBindItem(position: Int, request: KerabatTamu, onDecline: (id: Long, pos: Int) -> Unit,
                   onAccept: (id: Long, pos: Int) -> Unit) {
        val locationText = "${request.tamu.negara.name} - ${request.tamu.akomodasi.name}"

        Glide.with(view.context).load(request.tamu.avatar).centerCrop().into(view.avatar)
        view.name.text = request.tamu.name
        view.location.text = locationText
        view.btn_decline.setOnClickListener { onDecline(request.id, position) }
        view.btn_accept.setOnClickListener { onAccept(request.id, position) }
        view.container.setOnClickListener {
            val intent = Intent(view.context, ProfileKramaActivity::class.java)
            intent.putExtra("USERNAME", request.tamu.username!!)
            view.context.startActivity(intent)
        }
    }
}