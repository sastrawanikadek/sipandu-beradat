package com.sipanduberadat.user.viewHolders

import android.content.Intent
import android.view.View
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.sipanduberadat.user.activities.ProfileKramaActivity
import com.sipanduberadat.user.models.Kerabat
import com.sipanduberadat.user.models.Me
import kotlinx.android.synthetic.main.layout_request_family_item.view.*

class RequestFamilyViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    fun onBindItem(position: Int, request: Kerabat, me: Me, onDecline: (id: Long, pos: Int) -> Unit,
                   onAccept: (id: Long, pos: Int) -> Unit) {
        val locationText = "Banjar ${request.masyarakat.banjar.name}, " +
                "Desa Adat ${request.masyarakat.banjar.desa_adat.name}"

        Glide.with(view.context).load(request.masyarakat.avatar).centerCrop().into(view.avatar)
        view.name.text = request.masyarakat.name
        view.location.text = locationText
        view.btn_decline.setOnClickListener { onDecline(request.id, position) }
        view.btn_accept.setOnClickListener { onAccept(request.id, position) }
        view.container.setOnClickListener {
            val intent = Intent(view.context, ProfileKramaActivity::class.java)
            intent.putExtra("USERNAME", request.masyarakat.username!!)
            intent.putExtra("ME", me)
            view.context.startActivity(intent)
        }
    }
}