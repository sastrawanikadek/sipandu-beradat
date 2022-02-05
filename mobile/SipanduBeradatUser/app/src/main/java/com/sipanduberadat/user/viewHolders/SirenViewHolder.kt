package com.sipanduberadat.user.viewHolders

import android.view.View
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.sipanduberadat.user.models.Sirine
import kotlinx.android.synthetic.main.layout_item_siren.view.*

class SirenViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    fun onBindItem(siren: Sirine, onClick: (siren: Sirine) -> Unit) {
        val title = "Sirine ${siren.code}"

        Glide.with(view.context).load(siren.photo).centerCrop().into(view.photo)
        view.title.text = title
        view.location.text = siren.location
        view.container.setOnClickListener { onClick(siren) }
    }
}