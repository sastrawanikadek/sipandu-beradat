package com.sipanduberadat.guest.viewHolders

import android.content.Intent
import android.view.View
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.sipanduberadat.guest.activities.ProfileKramaActivity
import com.sipanduberadat.guest.models.KerabatTamu
import kotlinx.android.synthetic.main.layout_item_family.view.*

class FamilyViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    fun onBindItem(family: KerabatTamu) {
        Glide.with(view.context).load(family.tamu.avatar).centerCrop().into(view.avatar)
        view.name.text = family.tamu.name
        view.phone.text = family.tamu.phone
        view.container.setOnClickListener {
            val intent = Intent(view.context, ProfileKramaActivity::class.java)
            intent.putExtra("USERNAME", family.tamu.username!!)
            view.context.startActivity(intent)
        }
    }
}