package com.sipanduberadat.user.viewHolders

import android.content.Intent
import android.view.View
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.sipanduberadat.user.activities.ProfileKramaActivity
import com.sipanduberadat.user.models.Kerabat
import com.sipanduberadat.user.models.Me
import kotlinx.android.synthetic.main.layout_item_family.view.*

class FamilyViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    fun onBindItem(family: Kerabat, me: Me) {
        Glide.with(view.context).load(family.masyarakat.avatar).centerCrop().into(view.avatar)
        view.name.text = family.masyarakat.name
        view.phone.text = family.masyarakat.phone
        view.container.setOnClickListener {
            val intent = Intent(view.context, ProfileKramaActivity::class.java)
            intent.putExtra("USERNAME", family.masyarakat.username!!)
            intent.putExtra("ME", me)
            view.context.startActivity(intent)
        }
    }
}